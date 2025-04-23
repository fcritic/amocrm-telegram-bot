<?php

declare(strict_types=1);

namespace routes;

use Mezzio\Application;

/** @var Application $app */

$app->get('/api/files/{file_id}', \Telegram\Handler\FileProxyHandler::class);

$app->post('/api/telegram/webhook', [
    \Telegram\Middleware\TelegramWebhookMiddleware::class,
    \Telegram\Handler\TelegramWebhookHandler::class
]);
