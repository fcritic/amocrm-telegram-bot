<?php

declare(strict_types=1);

namespace AmoCRM\Service\Factory;

use Account\Repository\AccessTokenRepository;
use Account\Repository\AccountRepository;
use AmoCRM\OAuthConfig;
use AmoCRM\Service\OAuthService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class OAuthServiceFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): OAuthService
    {
        $accountRepo = $container->get(AccountRepository::class);
        $tokenRepo = $container->get(AccessTokenRepository::class);

        $factoryClient = new AmoCRMApiClientFactory(
            $container->get(OAuthConfig::class),
        );

        return new OAuthService($tokenRepo, $accountRepo, $factoryClient);
    }
}
