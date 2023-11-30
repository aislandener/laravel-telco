<?php

namespace Aislandener\Telco\Models;

class Address
{
    public function __construct(
        public int $sellerId,
        public int $prospectId,
        public int $addressId,
        public string $number,
        public string $typeAddress,
        public int $classificationAddressId,
        public ?string $complement,
        public ?string $reference,
    )
    {}

    public function toArray()
    {
        return [
            'idUsuario' => $this->sellerId,
            'idProspecto' => $this->prospectId,
            'idEndereco' => $this->addressId,
            'numero' => $this->number,
            'tipoEndereco' => $this->typeAddress,
            'idClassificacaoEndereco' => $this->classificationAddressId,
            'complemento' => $this->complement,
            'referencia' => $this->reference,
        ];
    }
}