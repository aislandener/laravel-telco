<?php

return [
    'url' => env('TELCO_URL', 'https://teste.adapter.net.br/'),
    'username' => env('TELCO_USERNAME'),
    'password' => env('TELCO_PASSWORD'),
    'timeout' => env('TELCO_TIMEOUT', 30),
    'recurrence' => [
        'key' => env('TELCO_CRYPT_KEY'),
        'cipher' => env('TELCO_CRYPT_CIPHER'),
    ]
];
