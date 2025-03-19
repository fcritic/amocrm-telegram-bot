<?php

declare(strict_types=1);

namespace AmoCRM\Handler\Factory;

use AmoCRM\Handler\OAuthAmoHandler;
use AmoCRM\Service\OAuthService;
use Doctrine\DBAL\ConnectionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Фабрика по созданию OAuthAmoHandler
 */
class OAuthAmoHandlerFactory
{
    /** @var OAuthService */
    protected OAuthService $oAuthService;

    /**
     * @param ContainerInterface $container container
     * @return OAuthAmoHandler Handler
     * @throws ConnectionException
     */
    public function __invoke(ContainerInterface $container): OAuthAmoHandler
    {
        try {
            $this->oAuthService = $container->get(OAuthService::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException(message: $e->getMessage());
        }

        return new OAuthAmoHandler(oAuthServices: $this->oAuthService);
    }
}
