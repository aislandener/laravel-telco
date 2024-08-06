<?php

namespace Aislandener\Telco\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

readonly class ClientService
{
    public function __construct(private PendingRequest $http)
    {
    }

    public function getAvailableDueDates(): Collection
    {
        return collect($this->http->get('ws/area/cliente/vencimentos/ativos')->json('data'));
    }

    public function getContractById(int $contractId)
    {
        return $this->http
            ->withUrlParameters([
                'contractId' => $contractId,
            ])
            ->get('ws/area/cliente/contrato/{contractId}')->collect();
    }

}
