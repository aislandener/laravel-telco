<?php

namespace Aislandener\Telco\Services;

use Aislandener\Telco\Exceptions\TelcoException;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Aislandener\Telco\Enums\ShiftInstallation;

readonly class OmnichannelService
{
    public function __construct(private PendingRequest $http)
    {}

    /**
     * @param int $serviceId
     * @param Carbon $date
     * @param ShiftInstallation $shift
     * @param int $days
     * @return Collection
     * @throws TelcoException
     */
    public function availableSchedulingDates(int $serviceId, Carbon $date, ShiftInstallation $shift, $days = 7): Collection
    {
        $data = $this->http->withUrlParameters([
            'serviceId' => $serviceId,
            'date' => $date->format('dmY'),
            'shift' => $shift->apiInfo()
        ])->get('ws/integracao/omnichannel/agenda_tecnica/configuracoes/atendimento/{serviceId}/data/{date}/turno/{shift}', [
            'qtdeDiasLimite' => $days
        ]);

        if($data->json('error')){
            throw new TelcoException($data->json('error'), 422);
        }

        return collect($data->json('data'));
    }

    /**
     * @param int $serviceId
     * @param int $dateId
     * @return Collection
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
