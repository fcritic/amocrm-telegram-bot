<?php

declare(strict_types=1);

namespace AmoCRM;

use AmoCRM\Handler\AmoJoWebhookHandler;
use AmoCRM\Handler\Factory\AmoJoWebhookHandlerFactory;
use AmoCRM\Handler\Factory\OAuthAmoHandlerFactory;
use AmoCRM\Handler\OAuthAmoHandler;
use AmoCRM\Middleware\AmoJoWebhookMiddleware;
use AmoCRM\Middleware\Factory\AmoJoWebhookMiddlewareFactory;
use AmoCRM\Service\Factory\AmoJoChannelFactory;
use AmoCRM\Service\Factory\AmoJoClientFactory;
use AmoCRM\Service\Factory\OAuthServiceFactory;
use AmoCRM\Service\OAuthService;
use AmoJo\Client\AmoJoClient;
use AmoJo\Models\Channel;
use AmoJo\Webhook\ParserWebHooks;
use App\BeanstalkConfig;
use Integration\Command\AmoJoQueueWorkerCommand;
use Integration\Producer\AmoJoQueueProducer;
use Integration\Worker\AmoJoQueueWorker;
use Integration\Worker\Factory\AmoJoQueueWorkerFactory;
use Psr\Container\ContainerInterface;

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
            'invokables' => [
                ParserWebHooks::class => ParserWebHooks::class,
            ],
            'factories' => [
                OAuthConfig::class => function (ContainerInterface $container) {
                    return new OAuthConfig($container);
                },
                OAuthAmoHandler::class => OAuthAmoHandlerFactory::class,
                AmoJoWebhookMiddleware::class => AmoJoWebhookMiddlewareFactory::class,
                AmoJoWebhookHandler::class => AmoJoWebhookHandlerFactory::class,
                OAuthService::class => OAuthServiceFactory::class,
                Channel::class => AmoJoChannelFactory::class,
                AmoJoClient::class => AmoJoClientFactory::class,
            ],
        ];
    }


}
