<?php

declare(strict_types=1);

namespace AmoCRM\Handler\Factory;

use AmoCRM\Handler\AmoJoWebhookHandler;
use Integration\Producer\AmoJoQueueProducer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Фабрика по созданию AmoJoWebhookHandler
 */
class AmoJoWebhookHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AmoJoWebhookHandler
     */
    public function __invoke(ContainerInterface $container): AmoJoWebhookHandler
    {
        try {
            return new AmoJoWebhookHandler(producer: $container->get(AmoJoQueueProducer::class));
        } catch (ContainerExceptionInterface | NotFoundExceptionInterface $e) {
            throw new RuntimeException(message: $e->getMessage());
        }
    }
}
