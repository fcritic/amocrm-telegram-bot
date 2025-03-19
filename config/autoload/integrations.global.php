<?php

declare(strict_types=1);

use App\Enum\IntegrationType;
use Integration\Middleware\LoggerMiddleware;

return [
    'type' => IntegrationType::TELEGRAM_AMOJO->value,
    'enabled' => true,
    'amocrm' => [
        'client_id' => $_ENV['CLIENT_ID'],
        'client_secret' => $_ENV['CLIENT_SECRET'],
        'redirect_uri' => $_ENV['REDIRECT_URI'],
        'amojo' => [
            'channel' => [
                'uid' => $_ENV['CHANNEL_ID'],
                'secret_key' => $_ENV['SECRET_KEY']
            ],
            'segment' => 'ru',
            'middleware' => [
                LoggerMiddleware::class,
            ]
        ],
    ],
    'external_gateway' => [
        'telegram_url' => $_ENV['TELEGRAM_URL']
    ]
];
