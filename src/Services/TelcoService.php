<?php

namespace Aislandener\Telco\Services;

use Aislandener\Telco\Exceptions\TelcoException;
use Exception;
use GuzzleHttp\Middleware;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;

class TelcoService
{
    private const TELCO_TOKEN = 'telco.token';

    /**
     * Verbs that can be replayed without duplicating anything upstream.
     */
    private const IDEMPOTENT_METHODS = ['GET', 'HEAD'];

    /**
     * Statuses where the upstream refused the request before doing any work.
     */
    private const RETRYABLE_STATUSES = [429, 502, 503, 504];

    public function __construct(
        private readonly string $url,
        private readonly string $username,
        private readonly string $password,
        private readonly string $recurrenceKey,
        private readonly string $recurrenceCipher,
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 5,
        private readonly int $tries = 3,
    ) {}

    public function address(?int $timeout = null): AddressService
    {
        return new AddressService($this->http($timeout));
    }

    public function commercial(?int $timeout = null): CommercialService
    {
        return new CommercialService($this->http($timeout));
    }

    public function client(?int $timeout = null): ClientService
    {
        return new ClientService($this->http($timeout));
    }

    public function omnichannel(?int $timeout = null): OmnichannelService
    {
        return new OmnichannelService($this->http($timeout));
    }

    public function telephony(?int $timeout = null): TelephonyService
    {
        return new TelephonyService($this->http($timeout));
    }

    public function financial(?int $timeout = null): FinancialService
    {
        return new FinancialService($this->http($timeout));
    }

    public function clientApp(?int $timeout = null): ClientAppService
    {
        return new ClientAppService($this->http($timeout));
    }

    public function card(?int $timeout = null): CardService
    {
        return new CardService($this->http($timeout), $this->recurrenceKey, $this->recurrenceCipher);
    }

    /**
     * Build a request for a single call. $timeout overrides the configured read
     * timeout for slow endpoints (contract submission, reports, ...).
     */
    private function http(?int $timeout = null): PendingRequest
    {
        $method = null;
        $tokenRefreshed = false;
        $connectionRetried = false;

        return Http::baseUrl($this->url)
            ->acceptJson()
            ->asJson()
            ->withBasicAuth($this->username, $this->password)
            ->connectTimeout($this->connectTimeout)
            ->timeout($timeout ?? $this->timeout)
            ->withHeader('Token', Cache::get(self::TELCO_TOKEN, ''))
            ->withMiddleware(Middleware::mapRequest(function (RequestInterface $request) use (&$method) {
                $method = $request->getMethod();

                return $request;
            }))
            ->retry(
                $this->tries,
                // Exponential backoff with jitter, so a struggling upstream is not hit by
                // every worker on the same 100ms beat.
                fn (int $attempt) => (2 ** ($attempt - 1)) * 250 + random_int(0, 250),
                when: function (Exception $exception, PendingRequest $request) use (&$method, &$tokenRefreshed, &$connectionRetried) {
                    // Expired token: refresh once and replay. Safe for any verb, the upstream
                    // rejected the request before processing it.
                    if ($this->hasStatus($exception, 401)) {
                        if ($tokenRefreshed) {
                            return false;
                        }

                        $tokenRefreshed = true;
                        $request->replaceHeaders(['Token' => $this->getNewToken()]);

                        return true;
                    }

                    // Anything else may already have been processed upstream: replaying a POST
                    // duplicates a contract, an activation or a payment.
                    if (! in_array($method, self::IDEMPOTENT_METHODS, true)) {
                        return false;
                    }

                    if ($exception instanceof ConnectionException) {
                        // Each timeout burns a full timeout window while holding the worker,
                        // which is how a slow upstream turns into an outage here. One replay.
                        if ($connectionRetried) {
                            return false;
                        }

                        return $connectionRetried = true;
                    }

                    return $this->hasStatus($exception, self::RETRYABLE_STATUSES);
                },
                throw: false,
            )
            ->throw(function (Response $response, RequestException $e) {
                if ($response->json('error')) {
                    throw new TelcoException($response->json('error'), $e->getCode(), $e);
                }
                if ($response->json('erro')) {
                    throw new TelcoException($response->json('erro'), $e->getCode(), $e);
                }
                throw $e;
            });
    }

    private function hasStatus(Exception $exception, int|array $status): bool
    {
        return $exception instanceof RequestException
            && in_array($exception->response->status(), (array) $status, true);
    }

    private function getNewToken(): string
    {
        $token = (string) Http::baseUrl($this->url)
            ->acceptJson()
            ->contentType('application/json')
            ->withBasicAuth($this->username, $this->password)
            ->connectTimeout($this->connectTimeout)
            ->timeout(10)
            ->get('ws/auth/token/gerar')
            ->json('token');

        Cache::set(self::TELCO_TOKEN, $token);

        return $token;
    }
}
