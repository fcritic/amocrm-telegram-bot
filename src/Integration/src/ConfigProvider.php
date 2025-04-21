<?php

declare(strict_types=1);

namespace Integration;

use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Integration\Command\AmoJoQueueWorkerCommand;
use Integration\Command\RefreshTokensCommand;
use Integration\Command\TelegramQueueWorkerCommand;
use Integration\Handler\InstallingWidgetHandler;
use Integration\Repository\ConversationRepository;
use Integration\Repository\ExternalUserRepository;
use Integration\Repository\Interface\ConversationRepositoryInterface;
use Integration\Repository\Interface\ExternalUserRepositoryInterface;
use Integration\Repository\Interface\MessageRepositoryInterface;
use Integration\Repository\MessageRepository;
use Psr\Container\ContainerInterface;

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
                ConversationRepositoryInterface::class => ConversationRepository::class,
                ExternalUserRepositoryInterface::class => ExternalUserRepository::class,
                MessageRepositoryInterface::class => MessageRepository::class,
            ],
            'factories' => [
                AmoJoQueueWorkerCommand::class => static function (ContainerInterface $container) {
                    return new AmoJoQueueWorkerCommand($container);
                },
                TelegramQueueWorkerCommand::class => static function (ContainerInterface $container) {
                    return new TelegramQueueWorkerCommand($container);
                },
                InstallingWidgetHandler::class => AttributedServiceFactory::class,
            ],
            'console' => [
                'commands' => [
                    'amojo:sync-message' => AmoJoQueueWorkerCommand::class,
                    'telegram:sync-message' => TelegramQueueWorkerCommand::class,
                    'refresh-tokens' => RefreshTokensCommand::class,
                ]
            ]
        ];
    }
}
