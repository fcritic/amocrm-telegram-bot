<?php

declare(strict_types=1);

namespace routes;

use Mezzio\Application;

const AMO_PREFIX = '/api/amocrm';

/** @var Application $app */

$app->get(AMO_PREFIX . '/installing-integration', \AmoCRM\Handler\InstallingIntegrationHandler::class);

$app->get(AMO_PREFIX . '/uninstalling-integration', [
    \AmoCRM\Handler\UninstallingIntegrationHandler::class,
    \AmoCRM\Middleware\UninstallingIntegrationMiddleware::class
]);

$app->post('/api/amocrm/webhook/amojo/{scope_id}', [
    \AmoCRM\Middleware\AmoJoWebhookMiddleware::class,
    \AmoCRM\Handler\AmoJoWebhookHandler::class
]);
