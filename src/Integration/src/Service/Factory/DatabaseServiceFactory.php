<?php

declare(strict_types=1);

namespace Integration\Service\Factory;

use Account\Repository\AccountRepository;
use Chat\Repository\ConversationRepository;
use Chat\Repository\ExternalUserRepository;
use Chat\Repository\MessageRepository;
use Doctrine\DBAL\ConnectionException;
use Integration\Service\DatabaseService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Telegram\Repository\TelegramConnectionRepository;
use Telegram\Service\Factory\TelegramBotApiFactory;

class DatabaseServiceFactory
{
    /**
     * @throws ConnectionException
     */
    public function __invoke(ContainerInterface $container): DatabaseService
    {
        try {
            return new DatabaseService(
                accountRepo: $container->get(AccountRepository::class),
                conversationRepo: $container->get(ConversationRepository::class),
                externalUserRepo: $container->get(ExternalUserRepository::class),
                messageRepo: $container->get(MessageRepository::class),
                telegramRepo: $container->get(TelegramConnectionRepository::class),
            );
        } catch (ContainerExceptionInterface $e) {
            throw new ConnectionException($e->getMessage());
        }
    }
}
