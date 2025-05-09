<?php

namespace Aislandener\Telco\Services;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

readonly class CardService
{

    public function __construct(private PendingRequest $http, private string $key, private string $cipher)
    {}

    protected function encryptData(mixed $data): string
    {
        return openssl_encrypt($data, $this->cipher, $this->key);
    }

    public function storeCard(string $clientId, array $contractsIds, string $cardFlag, string $nameOwner, string $numberCard, string $dueDate, string $lastDigits, string $securityCode): PromiseInterface|Response
    {
        return $this->http->post('/ws/financeiro/dados_cartao/recorrencia/cadastrar/externo',[
            'idCliente' => $clientId,
            'bandeiraCartao' => $cardFlag,
            'numeroCartao' => $this->encryptData($numberCard),
            'nomeTitular' => $this->encryptData($nameOwner),
            'codigoSeguranca' => $this->encryptData($securityCode),
            'ultimosDigitos' => $this->encryptData($lastDigits),
            'dataValidade' => $this->encryptData($dueDate),
            'idsContratos' => $contractsIds,
        ]);

    }

    public function deleteCard(string $cardId): PromiseInterface|Response
    {
        return $this->http->withUrlParameters([
            'cardId' => $cardId,
        ])->delete('ws/financeiro/dados_cartao/recorrencia/{cardId}/excluir');
    }

    public function paymentInvoiceByCard(int $invoiceId, string $numberCard, string $cardFlag, string $nameCard, string $securityCode, Carbon $dueDate,): Collection
    {
        return $this->http->put('ws/integracao/cielo/ecommerce/realizar/pagamento',[
            'IDFatura' => $invoiceId,
            'FormaPagamento' => "CREDITO",
            'NumeroCartao' => $numberCard,
            'Bandeira' => $cardFlag,
            'NomeImpressoCartao' => $nameCard,
            'CodigoSeguranca' => $securityCode,
            'ExpiracaoCartao' => $dueDate->format('m/Y'),
        ])->collect();
    }
}
