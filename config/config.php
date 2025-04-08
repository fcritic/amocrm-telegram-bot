<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
    'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    \Mezzio\Tooling\ConfigProvider::class,
    \Mezzio\Helper\ConfigProvider::class,
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,

    new ArrayProvider($cacheConfig),
    \Mezzio\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,

    AmoCRM\ConfigProvider::class,
    App\ConfigProvider::class,
    Telegram\ConfigProvider::class,
    Integration\ConfigProvider::class,

    class_exists(\Mezzio\Swoole\ConfigProvider::class)
        ? \Mezzio\Swoole\ConfigProvider::class
        : static function (): array {
            return [];
        },

    new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),

    new PhpFileProvider(realpath(__DIR__) . '/development.config.php.dist'),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
