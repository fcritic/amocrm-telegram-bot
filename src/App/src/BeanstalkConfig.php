<?php

declare(strict_types=1);

namespace App;

use Dot\DependencyInjection\Attribute\Inject;
use Pheanstalk\Pheanstalk;

class BeanstalkConfig
{
    /** @var Pheanstalk|null Коннект с сервером очередей */
    private ?Pheanstalk $connection;

    /**
     * Конфиг который создает коннект с сервером очередей.
     * Параметры конфигурации получающие из контейнера зависимости
     *
     * @param array $configBeanstalk
     */
    #[Inject('config.beanstalk')]
    public function __construct(readonly protected array $configBeanstalk)
    {
        $this->connection = Pheanstalk::create(
            $configBeanstalk['host'],
            $configBeanstalk['port'],
            $configBeanstalk['timeout']
        );
    }

    /** Коннект */
    public function getConnection(): ?Pheanstalk
    {
        return $this->connection;
    }
}
