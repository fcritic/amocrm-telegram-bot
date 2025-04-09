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

/**
 * Базовая команда для запуска воркеров.
 * Реализует общую логику инициализации, обработки ошибок и взаимодействия с очередями.
 * Для создания конкретной команды требуется реализовать абстрактные методы.
 */
abstract class AbstractQueueWorkerCommand extends Command
{
    /**
     * @param ContainerInterface $container DI-контейнер для разрешения зависимостей
     * @param string $commandName Уникальное имя команды (например: "app:process-orders")
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $commandName
    ) {
        parent::__construct($this->commandName);
    }

    /**
     * Настраивает базовые параметры команды:
     * - Имя команды
     * - Описание (реализуется в дочерних классах)
     */
    final protected function configure(): void
    {
        $this->setName($this->commandName)
            ->setDescription($this->getCommandDescription());
    }

    /**
     *  Основной метод выполнения команды:
     *  1. Инициализирует UI для вывода
     *  2. Загружает подключение к БД
     *  3. Получает и запускает воркер
     *  4. Обрабатывает исключения
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
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

    /**
     * Возвращает FQCN класса воркера, который будет обрабатывать задачи.
     * Пример: OrderProcessingWorker::class
     */
    abstract protected function getWorkerClass(): string;

    /**
     * Возвращает заголовок для отображения при запуске команды.
     * Пример: "Запуск обработки заказов из очереди"
     */
    abstract protected function getExecutionTitle(): string;

    /**
     * Возвращает описание команды для help-раздела.
     * Пример: "Обрабатывает заказы из очереди и синхронизирует с CRM"
     */
    abstract protected function getCommandDescription(): string;

    /**
     *  Инициализирует подключение к базе данных.
     *  Вызывается перед запуском воркера для гарантии работоспособности DB-слоя.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function bootstrapDatabase(): void
    {
        $dbConnection = $this->container->get(DatabaseBootstrapper::class);
        $dbConnection->bootstrap();
    }

    /**
     *  Получает экземпляр воркера из контейнера:
     *  1. Запрашивает сервис по классу из getWorkerClass()
     *  2. Проверяет соответствие интерфейсу QueueWorkerInterface
     *  3. Возвращает готовый к работе экземпляр
     *
     * @return QueueWorkerInterface
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

    /**
     *  Унифицированная обработка ошибок:
     *  - Форматирует вывод ошибки
     *  - Отображает стек вызовов
     *  - Возвращает код завершения Command::FAILURE
     *
     * @param SymfonyStyle $io
     * @param Throwable $e
     * @return void
     */
    private function handleError(SymfonyStyle $io, Throwable $e): void
    {
        $io->error([
            'Error: ' . $e->getMessage(),
            'File: '  . $e->getFile() . ':' . $e->getLine(),
            'Trace: ' . $e->getTraceAsString()
        ]);
    }
}
