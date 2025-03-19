<?php

declare(strict_types=1);

namespace App\Factory;

use AmoCRM\Client\AmoCRMApiClient;
use AmoJo\Client\AmoJoClient;
use AmoJo\Exception\NotFountException;
use AmoJo\Models\Channel;
use App\Enum\IntegrationType;
use Doctrine\DBAL\ConnectionException;
use Integration\IntegrationManager;
use Integration\Telegram\TelegramAmoJoIntegration;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Базовая фабрика для создания API amoCRM клиентов
 */
abstract class AbstractFactory
{
    /** @var AmoCRMApiClient клиент amoCRM api/v4 */
    protected AmoCRMApiClient $amoCRMClient;

    /** @var AmoJoClient клиент amoCRM API чатов */
    protected AmoJoClient $amoJoClient;

    /**
     * @param ContainerInterface $container
     * @throws ConnectionException
     */
    abstract public function __invoke(ContainerInterface $container);

    /**
     * Создания клиентов с параметрами и конфига
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ConnectionException
     */
    public function create(ContainerInterface $container): void
    {
        try {
            $integrationManager = $container->get(id: IntegrationManager::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new NotFountException(message: $e->getMessage());
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException(message: $e->getMessage());
        }

        /** @var TelegramAmoJoIntegration $integration */
        $integration = $integrationManager->get(name: IntegrationType::TELEGRAM_AMOJO->value);

        $amoConfig = $integration->getAmoCrmConfig();

        $this->amoCRMClient = new AmoCRMApiClient(
            clientId: $amoConfig['client_id'],
            clientSecret: $amoConfig['client_secret'],
            redirectUri: $amoConfig['redirect_uri']
        );

        $this->amoJoClient = new AmoJoClient(
            channel: new Channel(
                uid: $amoConfig['amojo']['channel']['uid'],
                secretKey: $amoConfig['amojo']['channel']['secret_key'],
            ),
            additionalMiddleware: $amoConfig['amojo']['middleware'],
            segment: $amoConfig['amojo']['segment'],
        );
    }
}
