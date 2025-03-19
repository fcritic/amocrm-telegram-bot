<?php

declare(strict_types=1);

namespace App\Database;

use Doctrine\DBAL\ConnectionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseBootstrapper implements BootstrapperInterface
{
    /** @var bool */
    private static bool $isBootstrapped = false;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected readonly ContainerInterface $container)
    {
    }

    /**
     * @return void
     * @throws ConnectionException
     */
    public function bootstrap(): void
    {
        if (self::$isBootstrapped) {
            return;
        }

        try {
            $config = $this->container->get('config')['database'];
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }

        $capsule = new Capsule();
        $capsule->addConnection($config);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        self::$isBootstrapped = true;
    }
}
