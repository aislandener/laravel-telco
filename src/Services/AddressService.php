<?php

namespace Aislandener\Telco\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

class AddressService
{
    public function __construct(private readonly PendingRequest $http)
    {}

    public function neighborhoods(int $cityId, string $cep): Collection
    {
        return collect($this->http
            ->withUrlParameters(['city' => $cityId])
            ->get('ws/terceiros/bairros/cidade/{city}')
            ->json('resposta')
        );
    }

    public function streets(int $neighborhoodId, string $cep): Collection
    {
        return collect($this->http
            ->withUrlParameters(['neighborhood' => $neighborhoodId])
            ->get('ws/terceiros/enderecos/bairro/{neighborhood}')
            ->json('resposta')
        );
    }
}