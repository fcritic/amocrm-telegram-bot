<?php

declare(strict_types=1);

namespace AmoCRM\Factory;

use AmoJo\Client\AmoJoClient;
use AmoJo\Models\Channel;
use Doctrine\DBAL\ConnectionException;
use Integration\Middleware\AmoJoLoggerMiddleware;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

readonly class AmoJoClientFactory
{
    /**
     * @throws ConnectionException
     */
    public function __invoke(ContainerInterface $container): AmoJoClient
    {
        try {
            $config = $container->get('config');
            $channel = $container->get(Channel::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }

        return new AmoJoClient(
            channel: $channel,
            additionalMiddleware: $config['amojo']['middleware'],
            segment: $config['amojo']['segment'],
        );
    }
}
