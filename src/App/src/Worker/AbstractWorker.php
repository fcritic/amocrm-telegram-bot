<?php

declare(strict_types=1);

namespace App\Worker;

use App\BeanstalkConfig;
use Integration\Worker\Interface\QueueWorkerInterface;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Базовый класс для реализации воркеров, обрабатывающих задачи из очереди Beanstalk.
 * Реализует:
 * - Подключение к очереди
 * - Цикл ожидания/обработки задач
 * - Базовую обработку ошибок
 * - Удаление успешно выполненных задач
 */
abstract class AbstractWorker implements QueueWorkerInterface
{
    /** @var Pheanstalk Текущее подключение к серверу очередей */
    protected Pheanstalk $connection;

    /**
     * @var string Имя очереди, которую обрабатывает воркер.
     * Должна быть переопределена в дочерних классах.
     */
    protected string $queue = 'default';

    /**
     * @param BeanstalkConfig $beanstalk Конфигурация подключения к Beanstalk.
     * Содержит хост, порт и таймауты.
     */
    public function __construct(BeanstalkConfig $beanstalk)
    {
        $this->connection = $beanstalk->getConnection();
    }

    /**
     * Основной цикл обработки задач:
     * 1. Подключается к указанной очереди ($this->queue)
     * 2. Игнорирует дефолтную очередь (default)
     * 3. Резервирует задачи из очереди
     * 4. Обрабатывает задачу через метод process()
     * 5. Удаляет успешно обработанную задачу
     * 6. В случае ошибки вызывает handleException()
     *
     * @param OutputInterface $output Интерфейс для логирования в консоль.
     */
    public function execute(OutputInterface $output): void
    {
        while (
            $job = $this->connection
                ->watchOnly($this->queue)
                ->ignore(PheanstalkInterface::DEFAULT_TUBE)
                ->reserve()
        ) {
            try {
                $this->process(json_decode(
                    $job->getData(),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ), $output);
            } catch (Throwable $e) {
                $this->handleException($e, $job);
            }
            $this->connection->delete($job);
        }
    }

    /**
     * Обработчик ошибок при выполнении задачи.
     * Выводит в консоль:
     * - Сообщение об ошибке
     * - Стек вызовов
     * - Данные задачи
     *
     * @param Throwable $exception Пойманное исключение
     * @param Job $job Задача, вызвавшая ошибку
     */
    private function handleException(Throwable $exception, Job $job): void
    {
        echo 'Error Unhandled exception' . $exception . PHP_EOL . $job->getData();
    }

    /**
     * Абстрактный метод для обработки задачи.
     * Должен быть реализован в дочерних классах.
     *
     * @param array $data Данные задачи (распаршенный JSON)
     * @param OutputInterface $output Интерфейс для логирования
     */
    abstract public function process(array $data, OutputInterface $output): void;
}
