<?php

declare(strict_types=1);

//use App\Helper\ConfigLoader;
use Illuminate\Database\Capsule\Manager as Capsule;
use Phpmig\Adapter;
use Pimple\Container;

//ConfigLoader::loadEnv();

$container = new Container();

$container['config'] = [
    'driver'    => $_ENV['DRIVER'],
    'host'      => $_ENV['MYSQL_HOST'],
    'database'  => $_ENV['MYSQL_DATABASE'],
    'username'  => $_ENV['MYSQL_USER'],
    'password'  => $_ENV['MYSQL_PASSWORD'],
    'charset'   => $_ENV['CHARSET'],
    'collation' => $_ENV['COLLATION'],
    'prefix'    => '',
];

$container['db'] = static function ($c) {
    $capsule = new Capsule();
    $capsule->addConnection($c['config']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['phpmig.adapter'] = static function ($c) {
    return new Adapter\Illuminate\Database($c['db'], 'migrations');
};

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;
