<?php

use EFive\Bale\Safir\SafirClient;

return [
    'default' => env('BALE_SAFIR_DEFAULT', 'default'),

    'base_url' => env('BALE_SAFIR_BASE_URL', SafirClient::BASE_URL),
    'timeout' => (int) env('BALE_SAFIR_TIMEOUT', 30),
    'connect_timeout' => (int) env('BALE_SAFIR_CONNECT_TIMEOUT', 10),

    'clients' => [
        'default' => [
            'api_access_key' => env('BALE_SAFIR_API_ACCESS_KEY'),
            'bot_id' => env('BALE_SAFIR_BOT_ID'),
        ],
    ],
];
