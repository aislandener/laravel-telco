<?php

namespace Aislandener\Telco\Models;

use Aislandener\Telco\Contracts\TelcoParams;
use Aislandener\Telco\Enums\TypePerson;
use Aislandener\Telco\Facades\Telco;
use Illuminate\Support\Collection;

class Combo implements TelcoParams
{
    public function __construct(
        public int        $sellerId,
        public int        $comboId,
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
        public ?int       $numberId,
        public array      $promo = [],
        public string     $loyalty = 'MESES_12',
        public int        $noCancelDueToDefault = 1,
        public int        $commercialOrigin = 15,
        public int        $canSuspend = 1,
        public int        $blockCollectCall = 0,
        public int        $blockDddCall = 0,
        public int        $blockCellPhoneCall = 0,
        public int        $blockInternationalCall = 0,
        public TypePerson $typePerson = TypePerson::Personal,
    )
    {
    }

    public function commitContractToClient(): array
    {
        $info = $this->getInfoServer()
            ->firstWhere('IdPacote', $this->comboId);

        $data = [
            'idTipoContrato' => $this->contractTypeId,
            'idCliente' => $this->clientId,
            'idUsuario' => $this->sellerId,
            'idsEnderecos' => $this->addressIds,
            'idVencimento' => $this->dueId,
            'idFormaCobranca' => $this->billingMethodId,
            'idOrigemComercial' => $this->commercialOrigin,
            'podeSuspender' => $this->canSuspend,
            'naoCancelaPorInadimplencia' => $this->noCancelDueToDefault,
            'idCanalVenda' => $this->sellerChannel,
            'fidelizacao' => $this->loyalty,
            'idPacote' => $this->comboId,
            'idSaidaCaixa' => $this->outputBoxId,
            'idCaixa' => $this->boxId,
            'valorContrato' => strval(round(collect($info['PlanosPacote'])->sum(fn($combo) => ($combo['ValorPlano'] - $combo['DescontoPacote'])), 2)),
            'dadosPlanosPacote' => collect($info['PlanosPacote'])->map(function ($plan) {
                $planner = [];
                if ($plan['PossuiPlanejamentoTributario']) {
                    $planner = [
                        'idPlanejamentoTributario' => Telco::commercial()->getPlannerTax($plan['IdPlano'])[0]['Id'],
                    ];
                }
                return array_merge([
                    'idPlano' => $plan['IdPlano'],
                    'valorContrato' => strval($plan['ValorPlano'] - $plan['DescontoPacote']),
                ], [
                    'idsPromocoes' => $this->promo
                ], $planner);
            }),
        ];

        if ($this->numberId) {
            $data = array_merge($data, [
                'idNumero' => $this->numberId,
                'bloquearLigacaoCobrar' => $this->blockCollectCall,
                'bloquearLigacaoDDD' => $this->blockDddCall,
                'bloquearLigacaoCelular' => $this->blockCellPhoneCall,
                'exibeListaTelefonica' => $this->blockInternationalCall,
            ]);
        }

        return $data;
    }

    public function commitPromoExists(array $data): array
    {
        return [
            'idFormaCobranca' => $this->billingMethodId,
            'idTipoContrato' => $this->contractTypeId,
            'idCidade' => $this->cityId,
            'idPerfil' => $data['sellerId'] ?? $this->sellerId,
            'tipoPessoa' => $this->typePerson->apiName(),
            'idPacote' => $this->comboId,
            'idsTecnologias' => $this->getTechnologyId(),
            'estado' => $data['state'],
            'idBairro' => $data['neighborhoodId'],
            'cep' => $data['cep'],
            'prePago' => $data['prepaid'],
        ];
    }

    public function getTechnologyId(): string
    {
        return Telco::commercial()
            ->getTechnologies()
            ->whereIn('descricao', collect($this->getInfoServer()['PlanosPacote'])
                ->pluck('Tecnologia')
                ->toArray())
            ->implode('id', '-');
    }

    public function getInfoServer(int $technologyId = 4): Collection
    {
        return collect(
            Telco::commercial()
                ->getCombos($this->contractTypeId, $this->cityId)
        );
    }
}
