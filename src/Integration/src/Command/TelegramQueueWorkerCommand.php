<?php

declare(strict_types=1);

namespace Integration\Command;

use Integration\Worker\TelegramQueueWorker;
use Psr\Container\ContainerInterface;

final class TelegramQueueWorkerCommand extends AbstractQueueWorkerCommand
{
    /**
     * Команда на запуск воркера
     *
     * Выполняется из контейнера application-backend
     *
     *  ``vendor/bin/laminas app:telegram-queue-worker``
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct(
            $container,
            'telegram:sync-message',
        );
    }

    protected function getWorkerClass(): string
    {
        return TelegramQueueWorker::class;
    }

    protected function getExecutionTitle(): string
    {
        return 'Telegram Webhook Queue Worker';
    }

    protected function getCommandDescription(): string
    {
        return 'Starts worker for processing Telegram webhook queue';
    }
}
