<?php

namespace Aislandener\Telco\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Aislandener\Telco\Exceptions\TelcoException;

readonly class TelephonyService
{
    public function __construct(private PendingRequest $http)
    {}

    public function getPrefix(bool $isPortability, int $city, ?string $prefix = null): Collection
    {
        $response = collect(
            $this->http->withUrlParameters([
                'cityId' => $city,
                'isPortability' => $isPortability ? '1' : '0',
            ])
                ->get('ws/telefonia/prefixos/cidade/{cityId}/portabilidade/{isPortability}')
                ->json('resposta')
        );

        if (!$isPortability) {
            $prefixTelco = $response->sortBy('id')->first();
        } else {
            $prefix = Str::substr($prefix, 2, -4);
            $prefixTelco = $response->where('prefixo', $prefix)->first();
        }

        return collect($prefixTelco);
    }

    /**
     * @param array $prefix
     * @param string|null $number
     * @return Collection
     * @throws TelcoException
     */
    public function getNumberTelephony(array $prefix, string $number = null): Collection
    {
        $numbers = collect(
            $this->http->withUrlParameters([
                'prefix' => $prefix['id']
            ])
                ->get('ws/telefonia/numeros/livres/prefixo/{prefix}')
                ->json('resposta')
        );

        if ($numbers->isEmpty()) {
            throw new TelcoException("Nenhum número disponível", 500);
        }

        return collect($numbers->random());
    }
}
