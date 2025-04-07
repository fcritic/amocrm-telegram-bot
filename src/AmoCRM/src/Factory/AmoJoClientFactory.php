<?php

declare(strict_types=1);

namespace AmoCRM\Service\Factory;

use AmoJo\Client\AmoJoClient;
use AmoJo\Models\Channel;
use Doctrine\DBAL\ConnectionException;
use Integration\Middleware\LoggerMiddleware;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class AmoJoClientFactory
{
    /**
     * @throws ConnectionException
     */
    public function __invoke(ContainerInterface $container): AmoJoClient
    {
        try {
            return new AmoJoClient(
                channel: $container->get(Channel::class),
                additionalMiddleware: [LoggerMiddleware::class],
                segment: $container->get('config')['amojo']['segment'],
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }
}
