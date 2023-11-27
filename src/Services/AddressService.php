<?php

namespace Aislandener\Telco\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

class AddressService
{
    public function __construct(private readonly PendingRequest $http)
    {
    }

    public function getCitiesFromState(string $state, string $cep = null): Collection
    {
        return collect(
            $this->http
                ->when($cep, fn(PendingRequest $http, string $value) => $http->withQueryParameters(['cep' => $value]))
                ->withUrlParameters([
                    'uf' => $state
                ])
                ->get('/ws/terceiros/cidades/uf/{uf}')
                ->json('resposta')
        );
    }

    public function neighborhoods(int $cityId, string $cep): Collection
    {
        return collect($this->http
            ->withUrlParameters(['city' => $cityId])
            ->get('ws/terceiros/bairros/cidade/{city}',[
                'cep' => $cep
            ])
            ->json('resposta')
        );
    }

    public function streets(int $neighborhoodId, string $cep): Collection
    {
        return collect($this->http
            ->withUrlParameters(['neighborhood' => $neighborhoodId])
            ->get('ws/terceiros/enderecos/bairro/{neighborhood}',[
                'cep' => $cep
            ])
            ->json('resposta')
        );
    }
}