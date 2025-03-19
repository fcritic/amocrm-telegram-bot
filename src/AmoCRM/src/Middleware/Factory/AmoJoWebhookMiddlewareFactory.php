<?php

declare(strict_types=1);

namespace AmoCRM\Middleware\Factory;

use AmoCRM\Middleware\AmoJoWebhookMiddleware;
use AmoCRM\OAuthConfig;
use Doctrine\DBAL\ConnectionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class AmoJoWebhookMiddlewareFactory
{
    /**
     * @throws ConnectionException
     */
    public function __invoke(ContainerInterface $container): AmoJoWebhookMiddleware
    {
        try {
            $secret = $container->get(OAuthConfig::class)->getSecretKey();
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException(message: $e->getMessage());
        }

        return new AmoJoWebhookMiddleware($secret);
    }
}
