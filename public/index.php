<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;

// Delegate static file requests back to the PHP built-in webserver
if (
    PHP_SAPI === 'cli-server'
    && array_key_exists('SCRIPT_FILENAME', $_SERVER)
    && $_SERVER['SCRIPT_FILENAME'] !== __FILE__
) {
    return false;
}

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/**
 * Self-called anonymous function that creates its own scope and keeps the global namespace clean.
 */
(static function () {
    /** @var ContainerInterface $container */
    $container = require 'config/container.php';

    // Загрузка ENV
    $dotenv = new Dotenv();
    $dotenv->load('.env');

    /** @var Application $app */
    $app = $container->get(Application::class);
    $factory = $container->get(MiddlewareFactory::class);

    // Execute programmatic/declarative middleware pipeline and routing
    // configuration statements
    (require 'config/pipeline.php')($app, $factory, $container);
    (require 'config/routes.php')($app, $factory, $container);

    $app->run();
})();
