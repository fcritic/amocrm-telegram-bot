<?php

declare(strict_types=1);

namespace Telegram\Handler\Factory;

use App\Factory\AbstractFactory;
use Doctrine\DBAL\ConnectionException;
use Integration\Producer\TelegramQueueProducer;
use Psr\Container\ContainerInterface;
use Telegram\Handler\TelegramWebhookHandler;

/**
 * Фабрика по созданию TelegramWebhookHandlerFactory
 */
class TelegramWebhookHandlerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container container
     * @return TelegramWebhookHandler Handler
     */
    public function __invoke(ContainerInterface $container): TelegramWebhookHandler
    {
        return new TelegramWebhookHandler($container->get(TelegramQueueProducer::class));
    }
}
