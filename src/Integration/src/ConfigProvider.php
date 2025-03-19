<?php

declare(strict_types=1);

namespace Integration;

use Account\Repository\AccountRepository;
use AmoCRM\Handler\AmoJoWebhookHandler;
use AmoCRM\Handler\Factory\AmoJoWebhookHandlerFactory;
use AmoCRM\OAuthConfig;
use AmoCRM\Service\Factory\AmoCRMApiClientFactory;
use App\BeanstalkConfig;
use Chat\Repository\ConversationRepository;
use Chat\Repository\ExternalUserRepository;
use Chat\Repository\MessageRepository;
use Integration\Command\AmoJoQueueWorkerCommand;
use Integration\Command\TelegramQueueWorkerCommand;
use Integration\Handler\Factory\SettingsIntegrationHandlerFactory;
use Integration\Handler\SettingsIntegrationHandler;
use Integration\Middleware\SettingsIntegrationMiddleware;
use Integration\Producer\AmoJoQueueProducer;
use Integration\Producer\TelegramQueueProducer;
use Integration\Service\DatabaseService;
use Integration\Service\Factory\DatabaseServiceFactory;
use Integration\Worker\AmoJoQueueWorker;
use Integration\Worker\Factory\AmoJoQueueWorkerFactory;
use Integration\Worker\Factory\TelegramQueueWorkerFactory;
use Integration\Worker\TelegramQueueWorker;
use Psr\Container\ContainerInterface;
use Telegram\Middleware\TelegramWebhookMiddleware;
use Telegram\Repository\TelegramRepository;
use Telegram\Service\Factory\TelegramBotApiFactory;
use Telegram\Service\TelegramSettingsService;

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
            'laminas-cli'  => $this->getCliConfig(),
        ];
    }

    /** @return array */
    public function getDependencies(): array
    {
        return [
            'invokables' => [

            ],
            'factories' => [
                DatabaseService::class => DatabaseServiceFactory::class,
                AmoJoQueueWorker::class  => AmoJoQueueWorkerFactory::class,
                AmoJoQueueProducer::class => static function (ContainerInterface $container) {
                    return new AmoJoQueueProducer($container->get(BeanstalkConfig::class));
                },
                TelegramQueueProducer::class => static function (ContainerInterface $container) {
                    return new TelegramQueueProducer($container->get(BeanstalkConfig::class));
                },
                AmoJoQueueWorkerCommand::class => static function (ContainerInterface $container) {
                    return new AmoJoQueueWorkerCommand($container);
                },
                TelegramQueueWorkerCommand::class => static function (ContainerInterface $container) {
                    return new TelegramQueueWorkerCommand($container);
                },
                TelegramSettingsService::class => function (ContainerInterface $container) {
                    return new TelegramSettingsService(
                        $container->get(TelegramBotApiFactory::class),
                        $container->get(TelegramRepository::class),
                        new AmoCRMApiClientFactory(
                            $container->get(OAuthConfig::class),
                        ),
                        $container->get('config')['external_gateway']['telegram_url'],
                    );
                },
                TelegramQueueWorker::class => TelegramQueueWorkerFactory::class,
                SettingsIntegrationMiddleware::class => function (ContainerInterface $container) {
                    return new SettingsIntegrationMiddleware($container->get(TelegramSettingsService::class));
                },
                TelegramWebhookMiddleware::class => function (ContainerInterface $container) {
                    return new TelegramWebhookMiddleware($container->get(TelegramSettingsService::class));
                },
                SettingsIntegrationHandler::class => SettingsIntegrationHandlerFactory::class,
            ],
        ];
    }

    /** return cli-commands list */
    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'app:amojo-queue-worker' => AmoJoQueueWorkerCommand::class,
                'app:telegram-queue-worker' => TelegramQueueWorkerCommand::class,
            ],
        ];
    }
}
