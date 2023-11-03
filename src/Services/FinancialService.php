<?php

namespace Aislandener\Telco\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;

class FinancialService
{
    public function __construct(private readonly PendingRequest $http)
    {}

    public function getBillingPayments(): Collection
    {
        return collect(
            $this->http->get('ws/financeiro/formas_pagamento')->json()
        );
    }

}