<?php

declare(strict_types=1);

namespace Integration\Command;

use Integration\Worker\AmoJoQueueWorker;
use Psr\Container\ContainerInterface;

/**
 * Команда для запуска воркера на amoJo
 */
final class AmoJoQueueWorkerCommand extends AbstractQueueWorkerCommand
{
    /**
     * Команда на запуск воркера commandName
     *
     * Выполняется из контейнера application-backend
     *
     *  ``php console.php amojo:sync-message``
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct(
            $container,
            'app:amojo:sync-message'
        );
    }

    /**
     * @return string
     */
    protected function getWorkerClass(): string
    {
        return AmoJoQueueWorker::class;
    }

    /**
     * @return string
     */
    protected function getExecutionTitle(): string
    {
        return 'AmoJo Webhook Queue Worker';
    }

    /**
     * @return string
     */
    protected function getCommandDescription(): string
    {
        return 'Starts worker for processing AmoJo webhook queue';
    }
}
