<?php

declare(strict_types=1);

namespace Integration\Producer;

use App\BeanstalkConfig;
use JsonException;
use Pheanstalk\Contract\PheanstalkInterface;

abstract class AbstractQueueProducer
{
    /** @var PheanstalkInterface|null Коннект с сервером очередей */
    private PheanstalkInterface|null $connection;

    public function __construct(BeanstalkConfig $beanstalk)
    {
        $this->connection = $beanstalk->getConnection();
    }

    /**
     * Продюсер вызывается в хендлере и отправляет задачи в только в указанную очередь.
     *
     * @param array $data
     * @return void
     * @throws JsonException
     */
    final public function produce(array $data): void
    {
        $this->connection
            ->useTube($this->getQueueName())
            ->put(json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * @return string
     */
    abstract protected function getQueueName(): string;
}
