<?php

declare(strict_types=1);

namespace AmoCRM\Factory;

use AmoCRM\AmoJoConfig;
use AmoJo\Client\AmoJoClient;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

readonly class AmoJoClientFactory
{
    /**
     * @param AmoJoConfig $config
     * @return AmoJoClient
     */
    public function __invoke(ContainerInterface $container): AmoJoClient
    {
        try {
            /** @var AmoJoConfig $config */
            $config = $container->get(AmoJoConfig::class);
        } catch (ContainerExceptionInterface $e) {
            throw new NotFoundResourceException($e->getMessage());
        }

        return new AmoJoClient(
            channel: $config->getChannel(),
            additionalMiddleware: $config->middleware,
            segment: $config->segment,
        );
    }
}
