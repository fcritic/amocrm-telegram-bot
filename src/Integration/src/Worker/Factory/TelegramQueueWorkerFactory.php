<?php

declare(strict_types=1);

namespace Integration\Worker\Factory;

use App\BeanstalkConfig;
use Doctrine\DBAL\ConnectionException;
use Integration\Service\DatabaseService;
use Integration\Worker\TelegramQueueWorker;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class TelegramQueueWorkerFactory
{
    /**
     * @throws ConnectionException
     */
    public function __invoke(ContainerInterface $container): TelegramQueueWorker
    {
        try {
            return new TelegramQueueWorker(
                $container->get(BeanstalkConfig::class),
                $container->get(DatabaseService::class),
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }
}
