<?php

declare(strict_types=1);

namespace App;

use App\Database\DatabaseBootstrapper;
use App\Middleware\DatabaseInitMiddleware;
use Psr\Container\ContainerInterface;

/**
 * ConfigProvider class
 */
class ConfigProvider
{
    /** @return array */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /** @return array */
    public function getDependencies(): array
    {
        return [
            'invokables' => [],
            'factories' => [
                DatabaseBootstrapper::class => static function (ContainerInterface $container) {
                    return new DatabaseBootstrapper($container);
                },
                DatabaseInitMiddleware::class => static function ($container) {
                    return new DatabaseInitMiddleware($container->get(DatabaseBootstrapper::class));
                },
                BeanstalkConfig::class => static function (ContainerInterface $container) {
                    return new BeanstalkConfig($container);
                }
            ],
        ];
    }
}
