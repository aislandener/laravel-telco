<?php

namespace Aislandener\Telco\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Aislandener\Telco\Enums\ShiftInstallation;

class OmnichannelService
{
    public function __construct(private readonly PendingRequest $http)
    {}

    public function availableSchedulingDates(int $serviceId, Carbon $date, ShiftInstallation $shift, $days = 7): Collection
    {
        return collect($this->http->withUrlParameters([
            'serviceId' => $serviceId,
            'date' => $date->format('dmY'),
            'shift' => $shift->apiInfo()
        ])->get('ws/integracao/omnichannel/agenda_tecnica/configuracoes/atendimento/{serviceId}/data/{date}/turno/{shift}', [
            'qtdeDiasLimite' => $days
        ])->json());
    }
}