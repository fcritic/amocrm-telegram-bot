<?php

declare(strict_types=1);

namespace Telegram;

use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Telegram\Factory\TelegramBotApiFactory;
use Telegram\Repository\Interface\TelegramConnectionRepositoryInterface;
use Telegram\Repository\TelegramConnectionRepository;
use Telegram\Service\TelegramBotService;

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
            'aliases' => [
                TelegramConnectionRepositoryInterface::class => TelegramConnectionRepository::class,
            ],
            'invokables' => [
                TelegramBotApiFactory::class => TelegramBotApiFactory::class,
            ],
            'factories' => [
                TelegramBotService::class => AttributedServiceFactory::class,
            ],
        ];
    }
}
