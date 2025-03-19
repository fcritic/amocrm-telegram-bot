<?php

declare(strict_types=1);

use AmoCRM\Handler\AmoJoWebhookHandler;
use AmoCRM\Middleware\AmoJoWebhookMiddleware;
use Integration\Handler\SettingsIntegrationHandler;
use Integration\Middleware\SettingsIntegrationMiddleware;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;
use Telegram\Handler\TelegramWebhookHandler;
use Telegram\Middleware\TelegramWebhookMiddleware;

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/api/oauth', \AmoCRM\Handler\OAuthAmoHandler::class);
    $app->get('/api/files/{file_id}', \Telegram\Handler\FileProxyHandler::class);

    $app->post('/api/webhook/amo/{scope_id}', [AmoJoWebhookMiddleware::class, AmoJoWebhookHandler::class]);
    $app->post('/api/webhook/telegram', [TelegramWebhookMiddleware::class, TelegramWebhookHandler::class]);
    $app->post('/api/webhook/token-telegram', [
        SettingsIntegrationMiddleware::class,
        SettingsIntegrationHandler::class
    ]);
};
