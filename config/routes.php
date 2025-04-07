<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/api/amocrm/installing-integration', \AmoCRM\Handler\InstallingIntegrationHandler::class);
    $app->post('/api/amocrm/installing-widget', [
        \Integration\Middleware\InstallingWidgetMiddleware::class,
        \Integration\Handler\InstallingWidgetHandler::class
    ]);
    $app->post('/api/amocrm/webhook/amojo/{scope_id}', [
        \AmoCRM\Middleware\AmoJoWebhookMiddleware::class,
        \AmoCRM\Handler\AmoJoWebhookHandler::class
    ]);
    $app->get('/api/files/{file_id}', \Telegram\Handler\FileProxyHandler::class);
    $app->post('/api/telegram/webhook', [
        \Telegram\Middleware\TelegramWebhookMiddleware::class,
        \Telegram\Handler\TelegramWebhookHandler::class
    ]);
};
