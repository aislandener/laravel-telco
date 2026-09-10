<?php

return [
    'url' => env('TELCO_URL', 'https://teste.adapter.net.br/'),
    'username' => env('TELCO_USERNAME'),
    'password' => env('TELCO_PASSWORD'),
    'timeout' => env('TELCO_TIMEOUT', 30),
    'connect_timeout' => env('TELCO_CONNECT_TIMEOUT', 5),
    'tries' => env('TELCO_TRIES', 3),
    'recurrence' => [
        'key' => env('TELCO_CRYPT_KEY'),
        'cipher' => env('TELCO_CRYPT_CIPHER'),
    ],
];
