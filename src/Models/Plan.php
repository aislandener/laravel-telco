<?php

namespace Aislandener\Telco\Models;

use Illuminate\Support\Collection;
use Aislandener\Telco\Contracts\TelcoParams;
use Aislandener\Telco\Enums\TypePerson;
use Aislandener\Telco\Facades\Telco;

class Plan implements TelcoParams
{

    public function __construct(
        public int        $sellerId,
        public int        $planId,
        public int        $billingMethodId,
        public int        $contractTypeId,
        public ?int       $cityId,
        public ?int       $clientId,
        public ?float     $contractValue,
        public ?array     $addressIds,
        public ?int       $dueId,
        public ?int       $sellerChannel,
        public ?int       $outputBoxId,
        public ?int       $boxId,
        public array      $promo = [],
        public string     $loyalty = 'MESES_12',
        public int        $noCancelDueToDefault = 1,
        public int        $commercialOrigin = 15,
        public int        $canSuspend = 1,
        public TypePerson $typePerson = TypePerson::Personal,
    )
    {
    }

    public function commitContractToClient(): array
    {
        return [
            'idUsuario' => $this->sellerId,
            'idPlano' => $this->planId,
            'idCliente' => $this->clientId,
            'valorContrato' => $this->contractValue,
            'idTipoContrato' => $this->contractTypeId,
            'idsEnderecos' => $this->addressIds,
            'idOrigemComercial' => $this->commercialOrigin,
            'idVencimento' => $this->dueId,
            'idFormaCobranca' => $this->billingMethodId,
            'idCanalVenda' => $this->sellerChannel,
            'podeSuspender' => $this->canSuspend,
            'naoCancelaPorInadimplencia' => $this->noCancelDueToDefault,
            'fidelizacao' => $this->loyalty,
            'idSaidaCaixa' => $this->outputBoxId,
            'idCaixa' => $this->boxId,
            'idsPromocoes' => $this->promo,
        ];
    }

    public function commitPromoExists(array $data): array
    {
        return [
            'idFormaCobranca' => $this->billingMethodId,
            'idTipoContrato' => $this->contractTypeId,
            'idCidade' => $this->cityId,
            'idPerfil' => $this->sellerId,
            'tipoPessoa' => $this->typePerson->apiName(),
            'idPlano' => $this->planId,
            'idTecnologia' => $this->getTechnologyId(),
            'estado' => $data['state'],
            'idBairro' => $data['neighborhoodId'],
            'cep' => $data['cep'],
            'prePago' => $data['prepaid'] ?? 2,
        ];
    }

    public function getTechnologyId(): string
    {
        return (string)Telco::commercial()
            ->getTechnologies()
            ->firstWhere('descricao', $this->getInfoServer()['Tecnologia'])['id'];
    }

    public function getInfoServer(int $technologyId = 4): Collection
    {
        return collect(
            Telco::commercial()
                ->getPlans($this->cityId, $technologyId, $this->contractTypeId, $this->typePerson)
                ->where('IdPlano', $this->planId)
                ->flatMap(fn($element) => $element)
        );
    }
}