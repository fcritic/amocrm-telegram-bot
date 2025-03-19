<?php

declare(strict_types=1);

namespace Integration\Worker\Factory;

use AmoJo\Client\AmoJoClient;
use AmoJo\Webhook\ParserWebHooks;
use App\BeanstalkConfig;
use Doctrine\DBAL\ConnectionException;
use Integration\Service\DatabaseService;
use Integration\Worker\AmoJoQueueWorker;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Telegram\Service\TelegramEventService;

class AmoJoQueueWorkerFactory
{
    /**
     * @throws ConnectionException
     */
    public function __invoke(ContainerInterface $container): AmoJoQueueWorker
    {
        try {
            return new AmoJoQueueWorker(
                beanstalk: $container->get(BeanstalkConfig::class),
                parserWebHook: $container->get(ParserWebHooks::class),
                telegramService: $container->get(TelegramEventService::class),
                databaseService: $container->get(DatabaseService::class),
                amoJoClient: $container->get(AmoJoClient::class),
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }
}
