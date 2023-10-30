<?php

namespace Aislandener\Telco\Models;

use Aislandener\Telco\Enums\TypePerson;
use Illuminate\Support\Carbon;

class Prospect
{
    public function __construct(
        public TypePerson $typePerson,
        public string     $name,
        public string     $fixedPhone,
        public string     $cellPhone,
        public Carbon     $birthDate,
        public int        $sellerId,
        public int        $prospectSegmentId,
        public int        $prospectTypeId,
        public int        $sellerChannelId,
        public int        $companyId,
        public string     $email,
        public ?string    $obsProspect = null,
        public ?string    $cpf = null,
        public ?string    $rg = null,
        public ?string    $motherName = null,
        public ?string    $gender = null,
        public ?string    $cnpj = null,
        public ?string    $stateRegistration = null,
        public ?string    $municipalRegistration = null,
        public ?string    $fantasyName = null,
    )
    {
    }

    public function toArray(): array
    {
        $data = [
            'tipoPessoa' => $this->typePerson->apiName(),
            'nome' => $this->name,
            'telefoneFixo' => preg_replace('/[^0-9]/', '', $this->fixedPhone),
            'telefoneMovel' => preg_replace('/[^0-9]/', '', $this->cellPhone),
            'dataNascimento' => $this->birthDate->format('d-m-Y'),
            'idVendedor' => $this->sellerId,
            'idSegmentoProspecto' => $this->prospectSegmentId,
            'idTipoProspecto' => $this->prospectTypeId,
            'idCanalVenda' => $this->sellerChannelId,
            'idEmpresa' => $this->companyId,
            'email' => $this->email,
            'observacaoProspecto' => $this->obsProspect
        ];

        return array_merge($data, match ($this->typePerson) {
            TypePerson::Personal => [
                'cpf' => $this->cpf,
                'rg' => $this->rg,
                'nomeMae' => $this->motherName,
                'sexo' => $this->gender,
            ],
            TypePerson::Business => [
                'cnpj' => $this->cnpj,
                'inscricaoEstadual' => $this->stateRegistration,
                'inscricaoMunicipal' => $this->municipalRegistration,
                'nomeFantasia' => $this->fantasyName
            ],
        });
    }
}