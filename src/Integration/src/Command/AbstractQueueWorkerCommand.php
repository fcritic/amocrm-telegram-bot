<?php

declare(strict_types=1);

namespace Integration\Command;

use App\Database\DatabaseBootstrapper;
use Exception;
use Integration\Worker\Interface\QueueWorkerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

abstract class AbstractQueueWorkerCommand extends Command
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $commandName
    ) {
        parent::__construct($this->commandName);
    }

    /** Задает параметры команды */
    final protected function configure(): void
    {
        $this->setName($this->commandName)
            ->setDescription($this->getCommandDescription());
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getExecutionTitle());

        try {
            $this->bootstrapDatabase();
            $worker = $this->resolveWorker();
            $worker->execute(new ConsoleOutput());
        } catch (Exception | ContainerExceptionInterface $e) {
            $this->handleError($io, $e);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    abstract protected function getWorkerClass(): string;

    abstract protected function getExecutionTitle(): string;

    abstract protected function getCommandDescription(): string;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function bootstrapDatabase(): void
    {
        $dbConnection = $this->container->get(DatabaseBootstrapper::class);
        $dbConnection->bootstrap();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolveWorker(): QueueWorkerInterface
    {
        $worker = $this->container->get($this->getWorkerClass());

        if (!$worker instanceof QueueWorkerInterface) {
            throw new RuntimeException(
                sprintf('Worker must implement %s', QueueWorkerInterface::class)
            );
        }

        return $worker;
    }

    private function handleError(SymfonyStyle $io, Throwable $e): void
    {
        $io->error([
            'Error: ' . $e->getMessage(),
            'File: '  . $e->getFile() . ':' . $e->getLine(),
            'Trace: ' . $e->getTraceAsString()
        ]);
    }
}
