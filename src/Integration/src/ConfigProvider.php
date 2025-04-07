<?php

declare(strict_types=1);

namespace Integration;

use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Integration\Command\AmoJoQueueWorkerCommand;
use Integration\Command\TelegramQueueWorkerCommand;
use Integration\Handler\InstallingWidgetHandler;
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
                ]
            ]
        ];
    }
}
