<?php

namespace App;

use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class BeanstalkConfig
{
    /** @var Pheanstalk|null Коннект с сервером очередей */
    private ?Pheanstalk $connection;

    /**
     * Конфиг который создает коннект с сервером очередей.
     * Параметры конфигурации получающие из контейнера зависимости
     *
     * @param ContainerInterface $container контейнер зависимостей
     */
    public function __construct(ContainerInterface $container)
    {
        try {
            $this->connection = Pheanstalk::create(
                $container->get('config')['beanstalk']['host'],
                $container->get('config')['beanstalk']['port'],
                $container->get('config')['beanstalk']['timeout']
            );
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            exit('BeanstalkConfig ' . $e->getMessage());
        }
    }

    /** Коннект */
    public function getConnection(): ?Pheanstalk
    {
        return $this->connection;
    }
}
