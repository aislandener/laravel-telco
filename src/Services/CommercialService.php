<?php

namespace Aislandener\Telco\Services;

use Aislandener\Telco\Contracts\TelcoParams;
use Aislandener\Telco\Enums\TypePerson;
use Aislandener\Telco\Models\Address;
use Aislandener\Telco\Models\Prospect;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

readonly class CommercialService
{
    public function __construct(private PendingRequest $http)
    {
    }

    public function registerProspect(Prospect $prospect): mixed
    {
        return $this->http->post('ws/comercial/prospectos/cadastrar', $prospect->toArray())->json();
    }

    public function updateProspect(Prospect $prospect): mixed
    {
        return $this->http->put('ws/comercial/prospectos/atualizar', $prospect->toArray())->json();
    }

    public function checkAddressDebitOnProspect(int $prospectId, int $sellerId): bool
    {
        $response = $this->http->post('ws/comercial/prospectos/credito_endereco/consultar', [
            'idProspecto' => $prospectId,
            'idUsuario' => $sellerId,
        ])->json('resposta');

        if (array_key_exists('possuiDivida', $response)) {
            return !$response['possuiDivida']; // true = não tem débito
        }

        return !array_key_exists('faturas', $response);  // false = tem débito
    }

    public function checkDebitOnProspect(int $prospectId, int $sellerId): bool
    {
        $response = $this->http->post('ws/comercial/prospectos/credito_interno/consultar', [
            'idProspecto' => $prospectId,
            'idUsuario' => $sellerId,
        ])->json('resposta');

        if (array_key_exists('possuiDivida', $response)) {
            return !$response['possuiDivida']; // true = não tem débito
        }

        return !array_key_exists('faturas', $response);  // false = tem débito
    }

    public function checkTechnicalViabilityOnProspect(int $prospectId, int $sellerId): bool
    {
        $response = $this->http->post('ws/comercial/prospectos/viabilidade_tecnica/consultar', [
            'idProspecto' => $prospectId,
            'idUsuario' => $sellerId,
        ])->json('resposta');

        return Str::contains($response['resultado'], 'APROVADO');
    }

    public function registerProspectAddress(Address $address): mixed
    {
        return $this->http->post('ws/comercial/prospectos/enderecos/cadastrar', $address->toArray())->json();
    }

    public function turnProspectIntoClient(
        int $prospectId,
        int $sellerId,
        int $issueInvoice = 1,
        int $singleInvoice = 1,
        int $sendInvoiceEmail = 0,
        int $issueSingleInvoice = 1,
        int $opinionFormer = 0,
        int $employee = 0,
        int $partner = 0,
        int $receiveSms = 1,
        int $typeAtividadeId = 3,
        int $cfopId = 1): mixed
    {
        return $this->http->post('ws/comercial/prospectos/converter/cliente', [
            'idProspecto' => $prospectId,
            'idUsuario' => $sellerId,
            'emiteNotaFiscal' => $issueInvoice,
            'emiteNotaUnica' => $singleInvoice,
            'enviaNotaFiscalEmail' => $sendInvoiceEmail,
            'emiteFaturaUnica' => $issueSingleInvoice,
            'formadorOpiniao' => $opinionFormer,
            'funcionario' => $employee,
            'parceiro' => $partner,
            'recebeSMS' => $receiveSms,
            'idTipoAtividade' => $typeAtividadeId,
            'idCfop' => $cfopId,
        ])->json();
    }

    public function getCombos(int $typeContractId, int $cityId): Collection
    {
        return collect(
            $this->http->withUrlParameters([
                'typeContractId' => $typeContractId,
                'cityId' => $cityId,
            ])
                ->get('ws/comercial/contratos/pacotes/tipo_contrato/{typeContractId}/cidade/{cityId}')
                ->json('resposta')
        );
    }

    public function getPlannerTax(int $planId, int $companyId = 1): Collection
    {
        return $this->http->withUrlParameters([
            'planId' => $planId,
            'companyId' => $companyId,
        ])
            ->get('ws/comercial/contratos/planejamentos_tributarios/plano/{planId}/empresa/{companyId}')
            ->collect('resposta');
    }

    public function getPlans(
        int        $cityId,
        int        $technologyId = 4,
        int        $typeContractId = 4,
        TypePerson $typePerson = TypePerson::Personal): Collection
    {
        return collect(
            $this->http
                ->withUrlParameters([
                    'technologyId' => $technologyId,
                    'typeContractId' => $typeContractId,
                    'typePerson' => $typePerson->apiName(),
                    'cityId' => $cityId,
                ])
                ->get('ws/comercial/contratos/planos/tecnologia/{technologyId}/tipo_contrato/{typeContractId}/tipo_pessoa/{typePerson}/cidade/{cityId}')
                ->json('resposta')
        );
    }

    public function registerContractOnClient(TelcoParams $client): mixed
    {
        return $this->http->post('ws/comercial/contratos/cadastrar', $client->commitContractToClient())->json('resposta');
    }

    public function getContractDetails(int $contractId): mixed
    {
        return $this->http->withQueryParameters(['somenteContrato' => 's'])
            ->post('ws/comercial/contrato/dados', [
                'idContrato' => $contractId,
            ])->json();
    }

    public function createTicket(
        int    $contractId,
        string $description = '[e-Commerce] Agendamento de instalação',
        int    $typeServiceId = 21): mixed
    {
        return $this->http->post('ws/comercial/atendimentos/cadastrar', [
            'idContrato' => $contractId,
            'descricao' => $description,
            'idTipoAtendimento' => $typeServiceId,
        ])->json();
    }

    public function getAvailableBoxes(int $addressClient, int $typeContractId): Collection
    {
        return collect(
            $this->http->withUrlParameters([
                'addressId' => $addressClient,
                'typeContractId' => $typeContractId,
            ])
                ->get('ws/comercial/clientes/enderecos/{addressId}/caixas/proximas/tipo_contrato/{typeContractId}')
                ->json('resposta')
        );
    }

    public function getClientAddress(int $clientId): Collection
    {
        return collect($this->http->withUrlParameters([
            'clientId' => $clientId,
        ])->get('ws/comercial/clientes/{clientId}/enderecos')->json('resposta'));
    }

    public function getTypesContracts(): Collection
    {
        return collect($this->http->get('ws/comercial/contratos/tipos')->json('resposta'));
    }

    public function getTechnologies(): Collection
    {
        return $this->http->get('ws/comercial/tecnologias')->collect('resposta');
    }

    public function getCoverageArea(int $cityId): Collection
    {
        return $this->http
            ->withUrlParameters(['cityId' => $cityId])
            ->get('ws/comercial/contratos/areas_cobertura/cidade/{cityId}')
            ->collect('resposta');
    }

    public function getSpeeds(int $planId, int $contractTypeId, int $coverageId, TypePerson $typePerson): Collection
    {
        return $this->http
            ->post('ws/comercial/contratos/tabela/precos/velocidades', [
                'idPlano' => $planId,
                'idTipoContrato' => $contractTypeId,
                'idAreaCobertura' => $coverageId,
                'tipoPessoa' => $typePerson->apiName(),
            ])
            ->collect('resposta');
    }

    public function getDiscounts(TelcoParams $client, array $data): Collection
    {
        return $this->http->post('ws/comercial/contratos/promocoes/ativas', $client->commitPromoExists($data))->collect('resposta');
    }

    public function getClientByCpf(string $cpfcnpj): Collection
    {
        return $this->http->post('ws/comercial/cliente/dados', ['cpfcnpj' => $cpfcnpj])->collect();
    }

    public function getClientDataInvoicesContractsByCPF(string $cpfcnpj, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $param = [];
        if ($startDate) {
            $param['dataInicioVencFatura'] = $startDate->format('d-m-Y');
        }
        if ($endDate) {
            $param['dataFimVencFatura'] = $endDate->format('d-m-Y');
        }

        return $this->http
            ->withQueryParameters($param)
            ->post('/ws/comercial/cliente/dados', [
                'cpfcnpj' => $cpfcnpj,
            ])->collect();
    }

    public function requestChangeContract(string $client_id, array $data)
    {
        return $this->http->post('/ws/comercial/alterarPerfil', array_merge([
            'IDCliente' => $client_id,
        ], $data))->collect();
    }

    public function enableTrustAgreement(string $contractId): Collection
    {
        return $this->http
            ->put('/ws/comercial/contrato/habilitar_confianca', [
                'idContrato' => $contractId
            ])
            ->collect();
    }
}
