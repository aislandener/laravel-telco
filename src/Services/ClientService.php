<?php

namespace Aislandener\Telco\Services;

use Illuminate\Http\Client\PendingRequest;

class ClientService
{
    public function __construct(private readonly PendingRequest $http)
    {}

    public function getAvailableDueDates(): mixed
    {
        return $this->http->get('ws/area/cliente/vencimentos/ativos')->json();
    }

}