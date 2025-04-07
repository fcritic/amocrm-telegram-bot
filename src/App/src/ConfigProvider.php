<?php

declare(strict_types=1);

namespace App;

use App\Database\BootstrapperInterface;
use App\Database\DatabaseBootstrapper;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

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
            ],
            'factories' => [
                DatabaseBootstrapper::class => AttributedServiceFactory::class,
                BeanstalkConfig::class => AttributedServiceFactory::class,
            ],
        ];
    }
}
