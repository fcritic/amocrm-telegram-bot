<?php

declare(strict_types=1);

use App\Enum\Modules;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    foreach (Modules::cases() as $module) {
        $path = sprintf('%s/routes/%s.php', __DIR__, $module->value);

        if (file_exists($path)) {
            require_once $path;
        }
    }
};
