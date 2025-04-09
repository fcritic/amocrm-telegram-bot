<?php

declare(strict_types=1);

namespace Integration\Command;

use Integration\Worker\TelegramQueueWorker;
use Psr\Container\ContainerInterface;

/**
 * Команда для запуска воркера на telegram
 */
final class TelegramQueueWorkerCommand extends AbstractQueueWorkerCommand
{
    /**
     * Команда на запуск воркера commandName
     *
     * Выполняется из контейнера application-backend
     *
     *  ``php console.php telegram:sync-message``
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

    /**
     * @return string
     */
    protected function getWorkerClass(): string
    {
        return TelegramQueueWorker::class;
    }

    /**
     * @return string
     */
    protected function getExecutionTitle(): string
    {
        return 'Telegram Webhook Queue Worker';
    }

    /**
     * @return string
     */
    protected function getCommandDescription(): string
    {
        return 'Starts worker for processing Telegram webhook queue';
    }
}
