<?php

namespace Aislandener\Telco\Services;

use Aislandener\Telco\Enums\TypeBilling;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

readonly class FinancialService
{
    public function __construct(private PendingRequest $http) {}

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
            ->withUrlParameters(['cpf_cnpj' => $cpf_cnpj])
            ->get('ws/financeiro/faturas/{cpf_cnpj}/todas')
            ->collect();
    }

    public function getAllInvoicesSimple(string $cpf_cnpj): Collection
    {
        return $this->http
            ->withUrlParameters(['cpf_cnpj' => $cpf_cnpj])
            ->get('ws/financeiro/faturas/{cpf_cnpj}/simplificado')
            ->collect();
    }

    public function getCardsRegistersByClient(string $clientId): Collection
    {
        return $this->http
            ->withUrlParameters(['client_id' => $clientId])
            ->get('ws/financeiro/dados_cartao/recorrencia/cliente/{client_id}/buscar')
            ->collect('resposta');
    }

    public function getCardInformation(string $cardNumber, string $invoiceId = '1'): Collection
    {
        return $this->http
            ->withQueryParameters(['numeroCartao' => $cardNumber,
                'idFatura' => $invoiceId])
            ->get('ws/integracao/cielo/ecommerce/informacao_cartao')
            ->collect();
    }

    public function getPix(string $documentNumber, int $clientId, int $invoiceId, string $name, string $documentType): Collection
    {
        return $this->http
            ->post('ws/financeiro/pix/buscar', [
                'idFatura' => $invoiceId,
                'idCliente' => $clientId,
                'nomeCliente' => $name,
                'documentoCliente' => $documentNumber,
                'tipoDocumentoCliente' => $documentType,
            ])
            ->collect('resposta');
    }

    public function downloadInvoice($invoice_id, string $path, string $username, string $password): PromiseInterface|Response
    {
        return $this->http->accept('*/*')
            ->withUrlParameters([
                'invoiceId' => $invoice_id,
            ])
            ->sink($path)
            ->post('/ws/financeiro/fatura/{invoiceId}', [
                'usuario' => $username,
                'senha' => $password,
            ]);
    }

    public function downloadAnnualPayment(string $username, string $password, string $clientId, Carbon $startDate, Carbon $endDate, string $path): PromiseInterface|Response
    {
        return $this->http->accept('*/*')
            ->withQueryParameters([
                'dataInicio' => $startDate->format('d-m-Y'),
                'dataFim' => $endDate->format('d-m-Y'),
                'idCliente' => $clientId,
            ])
            ->sink($path)
            ->post('ws/financeiro/faturamento/quitacao_anual_debito', [
                'usuario' => $username,
                'senha' => $password,
            ]);

    }
}
