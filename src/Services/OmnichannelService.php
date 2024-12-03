<?php

namespace Aislandener\Telco\Services;

use Aislandener\Telco\Enums\ShiftInstallation;
use Aislandener\Telco\Exceptions\TelcoException;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

readonly class OmnichannelService
{
    public function __construct(private PendingRequest $http) {}

    /**
     * @param  int  $days
     *
     * @throws TelcoException
     */
    public function availableSchedulingDates(int $serviceId, Carbon $date, ShiftInstallation $shift, $days = 7): Collection
    {
        $data = $this->http->withUrlParameters([
            'serviceId' => $serviceId,
            'date' => $date->format('dmY'),
            'shift' => $shift->apiInfo(),
        ])->get('ws/integracao/omnichannel/agenda_tecnica/configuracoes/atendimento/{serviceId}/data/{date}/turno/{shift}', [
            'qtdeDiasLimite' => $days,
        ]);

        if ($data->json('error')) {
            throw new TelcoException($data->json('error'), 422);
        }

        return collect($data->json('data'));
    }

    /**
     * @throws TelcoException
     */
    public function scheduleInstallation(int $serviceId, int $dateId): Collection
    {
        return collect($this->http->put('ws/integracao/omnichannel/agenda_tecnica/configuracoes/efetivar', [
            'idAtendimento' => $serviceId,
            'idItemConfiguracaoAgendaTecnica' => $dateId,
        ])->json());
    }
}
