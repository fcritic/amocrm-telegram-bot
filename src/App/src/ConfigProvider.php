<?php

declare(strict_types=1);

namespace App;

use App\Database\BootstrapperInterface;
use App\Database\DatabaseBootstrapper;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * ConfigProvider class
 */
class ConfigProvider
{
    /** @return array */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /** @return array */
    public function getDependencies(): array
    {
        return [
            'abstract_factories' => [
                ReflectionBasedAbstractFactory::class,
            ],
            'aliases' => [
                BootstrapperInterface::class => DatabaseBootstrapper::class,
                LoggerInterface::class => Logger::class,
            ],
            'factories' => [
                DatabaseBootstrapper::class => AttributedServiceFactory::class,
                BeanstalkConfig::class => AttributedServiceFactory::class,
                Logger::class => \App\LoggerFactory::class,

                // Для каналов
                'logger.amocrm' => [\App\LoggerFactory::class, 'amocrm'],
                'logger.telegram' => [\App\LoggerFactory::class, 'telegram'],
            ],
        ];
    }
}
