<?php

declare(strict_types=1);

namespace Telegram;

use Account\Repository\AccountRepository;
use AmoCRM\OAuthConfig;
use AmoCRM\Service\Factory\AmoCRMApiClientFactory;
use GuzzleHttp\Client;
use Integration\Handler\Factory\SettingsIntegrationHandlerFactory;
use Integration\Handler\SettingsIntegrationHandler;
use Integration\Middleware\SettingsIntegrationMiddleware;
use Psr\Container\ContainerInterface;
use Telegram\Handler\Factory\FileProxyHandlerFactory;
use Telegram\Handler\Factory\TelegramWebhookHandlerFactory;
use Telegram\Handler\FileProxyHandler;
use Telegram\Handler\TelegramWebhookHandler;
use Telegram\Middleware\TelegramWebhookMiddleware;
use Telegram\Repository\TelegramRepository;
use Telegram\Service\Factory\TelegramBotApiFactory;
use Telegram\Service\FileService;
use Telegram\Service\TelegramEventService;
use Telegram\Service\TelegramSettingsService;

/**
 * ConfigProvider class
 */
class ConfigProvider
{
    /** @return array */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /** @return array */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                TelegramRepository::class => TelegramRepository::class,
                TelegramBotApiFactory::class => TelegramBotApiFactory::class,
            ],
            'factories' => [
                TelegramEventService::class => function (ContainerInterface $container) {
                    return new TelegramEventService(
                        $container->get(TelegramBotApiFactory::class),
                        $container->get(AccountRepository::class)
                    );
                },
                FileService::class => function (ContainerInterface $container) {
                    return new FileService(
                        new Client(),
                        $container->get(TelegramBotApiFactory::class),
                        $container->get(TelegramRepository::class)
                    );
                },
                TelegramWebhookHandler::class => TelegramWebhookHandlerFactory::class,
                FileProxyHandler::class => FileProxyHandlerFactory::class,
            ],
        ];
    }
}
