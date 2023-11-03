<?php

namespace Aislandener\Telco\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\Pure;
use Aislandener\Telco\Exceptions\TelcoException;

class TelcoService
{

    private PendingRequest $http;

    private const TELCO_TOKEN = 'telco.token';

    public function __construct(
        string $url,
        string $username,
        string $password
    )
    {
        $this->http = Http::baseUrl($url)
            ->acceptJson()
            ->asJson()
            ->withBasicAuth($username, $password)
            ->timeout(10)
            ->withHeader('Token', Cache::get(self::TELCO_TOKEN, ''))
            ->retry( 5, 100, when: function (Exception $exception, PendingRequest $request) use ($url, $username, $password) {
                if ($exception instanceof ConnectionException)
                    return true;
                if (!$exception instanceof RequestException ||
                    $exception->response->status() !== 401)
                    return false;

                $request->replaceHeaders(['Token' => $this->getNewToken($url, $username, $password)]);

                return true;
            }, throw: false)
            ->throw(function (Response $response, RequestException $e){
                if ($response->json('error'))
                    throw new TelcoException($response->json('error'), $e->getCode(), $e);
                throw $e;
            });
    }

    private function getNewToken(string $url, string $username, string $password): string
    {
        $http = Http::baseUrl($url)
            ->acceptJson()
            ->contentType('application/json')
            ->withBasicAuth($username, $password)
            ->timeout(10)
            ->get('ws/auth/token/gerar');

        Cache::set(self::TELCO_TOKEN, $http->json('token'));

        return Cache::get(self::TELCO_TOKEN);
    }

    #[Pure] public function address(): AddressService
    {
        return new AddressService($this->http);
    }

    #[Pure] public function commercial(): CommercialService
    {
        return new CommercialService($this->http);
    }

    #[Pure] public function client(): ClientService
    {
        return new ClientService($this->http);
    }

    #[Pure] public function omnichannel() : OmnichannelService
    {
        return new OmnichannelService($this->http);
    }

    #[Pure] public function telephony(): TelephonyService
    {
        return new TelephonyService($this->http);
    }



}