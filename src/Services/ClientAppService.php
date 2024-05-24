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
        return $this->http->put('/ws/area/cliente/resetar_senha',[
            'CPFCNPJ' => $cpfcnpj,
            'IDCliente' => $clientId,
        ]);
    }

}
