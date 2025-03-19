<?php

declare(strict_types=1);

namespace Telegram\Handler\Factory;

use Psr\Container\ContainerInterface;
use Telegram\Handler\FileProxyHandler;
use Telegram\Service\FileService;

class FileProxyHandlerFactory
{
    public function __invoke(ContainerInterface $container): FileProxyHandler
    {
        return new FileProxyHandler($container->get(FileService::class));
    }
}
