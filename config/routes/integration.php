<?php

declare(strict_types=1);

namespace routes;

use Mezzio\Application;

/** @var Application $app */

$app->post(AMO_PREFIX . '/installing-widget', [
    \Integration\Middleware\InstallingWidgetMiddleware::class,
    \Integration\Handler\InstallingWidgetHandler::class
]);
