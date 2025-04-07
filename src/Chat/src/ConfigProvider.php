<?php

declare(strict_types=1);

namespace Chat;

use Chat\Repository\ConversationRepository;
use Chat\Repository\ExternalUserRepository;
use Chat\Repository\Interface\ConversationRepositoryInterface;
use Chat\Repository\Interface\ExternalUserRepositoryInterface;
use Chat\Repository\Interface\MessageRepositoryInterface;
use Chat\Repository\MessageRepository;

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
                ConversationRepositoryInterface::class => ConversationRepository::class,
                ExternalUserRepositoryInterface::class => ExternalUserRepository::class,
                MessageRepositoryInterface::class => MessageRepository::class,
            ],
        ];
    }
}
