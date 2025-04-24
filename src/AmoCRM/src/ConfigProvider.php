<?php

declare(strict_types=1);

namespace AmoCRM;

use AmoCRM\Factory\AmoJoClientFactory;
use AmoCRM\Factory\MessageFactory;
use AmoCRM\Factory\SenderFactory;
use AmoCRM\Middleware\AmoJoWebhookMiddleware;
use AmoCRM\OAuth\OAuthConfigInterface;
use AmoCRM\Repository\AccessTokenRepository;
use AmoCRM\Repository\AccountRepository;
use AmoCRM\Repository\Interface\AccessTokenRepositoryInterface;
use AmoCRM\Repository\Interface\AccountRepositoryInterface;
use AmoCRM\Service\AmoCrmClientService;
use AmoJo\Client\AmoJoClient;
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
                AccessTokenRepositoryInterface::class => AccessTokenRepository::class,
                AccountRepositoryInterface::class => AccountRepository::class,
                OAuthConfigInterface::class => OAuthConfig::class,
            ],
            'invokables' => [
                ParserWebHooks::class => ParserWebHooks::class,
            ],
            'factories' => [
                AmoJoClient::class => AmoJoClientFactory::class,
                AmoJoWebhookMiddleware::class => AttributedServiceFactory::class,
                OAuthConfig::class => AttributedServiceFactory::class,
                MessageFactory::class => AttributedServiceFactory::class,
                SenderFactory::class => AttributedServiceFactory::class,
                AmoJoConfig::class => AttributedServiceFactory::class,
                AmoCrmClientService::class => AttributedServiceFactory::class
            ],
        ];
    }
}
