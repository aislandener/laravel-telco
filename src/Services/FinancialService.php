<?php

namespace Aislandener\Telco\Services;

use Aislandener\Telco\Enums\TypeBilling;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

readonly class FinancialService
{
    public function __construct(private PendingRequest $http)
    {}

    public function getPaymentMethods(): Collection
    {
        return collect(
            $this->http->get('ws/financeiro/formas_pagamento')->json()
        );
    }

    public function getBillingMethods(int $cityId, TypeBilling $typeBilling = TypeBilling::Ticket): Collection
    {
        return collect(
            $this->http
                ->withUrlParameters([
                    'cityId' => $cityId,
                    'type' => $typeBilling->value,
                ])->get('ws/financeiro/formas_cobranca/cidade/{cityId}/tipo/{type}')
                ->json('resposta')
        );
    }

    public function getAllInvoicesByCpf(string $cpf_cnpj): Collection
    {
        return $this->http
            ->withUrlParameters(['cpf_cnpj' => $cpf_cnpj,])
            ->get('ws/financeiro/faturas/{cpf_cnpj}/todas')
            ->collect();
    }

}
