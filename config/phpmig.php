<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Phpmig\Adapter;
use Pimple\Container;

$config = require __DIR__ . '/config.php';
$container = new Container();

$container['config'] = $config;

$container['db'] = static function (Container $c) {
    $capsule = new Capsule();
    $capsule->addConnection($c['config']['database']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['phpmig.adapter'] = static function (Container $c) {
    return new Adapter\Illuminate\Database($c['db'], 'migrations');
};

$container['phpmig.migrations_path'] = __DIR__ .  '/../migrations';

return $container;
