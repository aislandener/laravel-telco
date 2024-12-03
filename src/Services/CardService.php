<?php

namespace Aislandener\Telco\Services;

use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Client\PendingRequest;

readonly class CardService
{

    public function __construct(private PendingRequest $http, private string $key, private string $cipher)
    {}

    protected function encryptData(mixed $data): string
    {
        $encrypter = new Encrypter($this->key, $this->cipher);
        return $encrypter->encrypt($data);
    }

    public function registerCard(string $clientId, array $contractsIds, string $cardFlag, string $nameOwner, string $numberCard, string $dueDate, string $lastDigits, string $securityCode)
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
}
