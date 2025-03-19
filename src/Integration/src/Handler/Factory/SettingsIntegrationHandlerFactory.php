<?php

declare(strict_types=1);

namespace Integration\Handler\Factory;

use Doctrine\DBAL\ConnectionException;
use Integration\Handler\SettingsIntegrationHandler;
use Integration\Service\DatabaseService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Telegram\Service\TelegramSettingsService;

class SettingsIntegrationHandlerFactory
{
    /**
     * @param ContainerInterface $container ContainerInterface
     * @return SettingsIntegrationHandler SettingsIntegrationHandler
     * @throws ConnectionException
     */
    public function __invoke(ContainerInterface $container): SettingsIntegrationHandler
    {
        try {
            return new SettingsIntegrationHandler(
                $container->get(TelegramSettingsService::class),
                $container->get(DatabaseService::class)
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }
}
