<?php

declare(strict_types=1);

namespace AmoCRM;

use AmoCRM\Factory\AmoJoChannelFactory;
use AmoCRM\Factory\AmoJoClientFactory;
use AmoCRM\Factory\MessageFactory;
use AmoCRM\Factory\SenderFactory;
use AmoCRM\Middleware\AmoJoWebhookMiddleware;
use AmoCRM\OAuth\OAuthConfigInterface;
use AmoJo\Client\AmoJoClient;
use AmoJo\Models\Channel;
use AmoJo\Webhook\ParserWebHooks;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;

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
            'aliases' => [
                OAuthConfigInterface::class => OAuthConfig::class,
            ],
            'invokables' => [
                ParserWebHooks::class => ParserWebHooks::class,
            ],
            'factories' => [
                Channel::class => AmoJoChannelFactory::class,
                AmoJoClient::class => AmoJoClientFactory::class,
                AmoJoWebhookMiddleware::class => AttributedServiceFactory::class,
                OAuthConfig::class => AttributedServiceFactory::class,
                MessageFactory::class => AttributedServiceFactory::class,
                SenderFactory::class => AttributedServiceFactory::class,
            ],
        ];
    }
}
