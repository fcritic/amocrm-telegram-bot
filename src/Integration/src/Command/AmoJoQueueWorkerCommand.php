<?php

declare(strict_types=1);

namespace Integration\Command;

use Integration\Worker\AmoJoQueueWorker;
use Psr\Container\ContainerInterface;

final class AmoJoQueueWorkerCommand extends AbstractQueueWorkerCommand
{
    /**
     * Команда на запуск воркера
     *
     * Выполняется из контейнера application-backend
     *
     *  ``vendor/bin/laminas app:amojo-queue-worker``
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct(
            $container,
            'app:amojo-queue-worker'
        );
    }

    protected function getWorkerClass(): string
    {
        return AmoJoQueueWorker::class;
    }

    protected function getExecutionTitle(): string
    {
        return 'AmoJo Webhook Queue Worker';
    }

    protected function getCommandDescription(): string
    {
        return 'Starts worker for processing AmoJo webhook queue';
    }
}
