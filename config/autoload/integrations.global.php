<?php

declare(strict_types=1);

use App\Enum\IntegrationType;
use Integration\Middleware\AmoJoLoggerMiddleware;

return [
    'type' => IntegrationType::TELEGRAM_AMOJO,
    'enabled' => true,
    'host' => 'https://rich-novel-jackal.ngrok-free.app',
    'amocrm' => [
        'client_id' => $_ENV['CLIENT_ID'],
        'client_secret' => $_ENV['CLIENT_SECRET'],
        'redirect_uri' => $_ENV['REDIRECT_URI'],
    ],
    'amojo' => [
        'channel_uid' => $_ENV['CHANNEL_ID'],
        'secret_key' => $_ENV['SECRET_KEY'],
        'channel_code' => $_ENV['CHANNEL_CODE'],
        'segment' => 'ru',
        'middleware' => [
            AmoJoLoggerMiddleware::class,
        ]
    ],
    'external_gateway' => [
        'telegram_url' => $_ENV['TELEGRAM_URL']
    ]
];
