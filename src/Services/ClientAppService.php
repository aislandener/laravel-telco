<?php

namespace Aislandener\Telco\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

readonly class ClientAppService
{

    public function __construct(private PendingRequest $http)
    {
    }

    public function loginFull(string $username, string $password): PromiseInterface|Response
    {
        return $this->http->post('/ws/comercial/logar', ['usuario' => $username, 'senha' => $password]);
    }

    public function loginSimple(string $username, string $password): PromiseInterface|Response
    {
        return $this->http->post('/ws/comercial/clientes/logar', ['usuario' => $username, 'senha' => $password]);
    }

    public function resetPassword(string $cpfcnpj, int $clientId): PromiseInterface|Response
    {
        return $this->http->put('/ws/area/cliente/resetar_senha', [
            'CPFCNPJ' => $cpfcnpj,
            'IDCliente' => $clientId,
        ]);
    }

    public function getInvoices(string $username, string $password, string $clientId)
    {
        return $this->http->withUrlParameters([
            'clientId' => $clientId
        ])->post('/ws/comercial/contratos/cliente/{clientId}', [
            'usuario' => $username,
            'senha' => $password,
        ])->json();
    }

    public function changeDueDate(string $username, string $password, string $clientId, int $nowDueDateId, int $newDueDateId): PromiseInterface|Response
    {
        return $this->http->withUrlParameters([
            'clientId' => $clientId,
        ])->withQueryParameters([
            'vencimentoAtual' => $nowDueDateId,
            'novoVencimento' => $newDueDateId,
        ])->post('/ws/comercial/cliente/{clientId}/alterar/vencimento_contrato', [
            'usuario' => $username,
            'senha' => $password,
        ]);
    }

    public function getContractsAndInvoices(string $username, string $password, string $clientId)
    {
        return $this->http->withUrlParameters([
            'clientId' => $clientId,
        ])->post('/ws/comercial/contratos/cliente/{clientId}', [
            'usuario' => $username,
            'senha' => $password,
        ])->collect();
    }

    public function downloadPdfContract(string $contract_id, string $path)
    {
        return $this->http->accept('*/*')
            ->withUrlParameters([
                'contractId' => $contract_id,
            ])
            ->sink($path)
            ->get('/ws/comercial/contratos/modelo/{contractId}');

    }

}
