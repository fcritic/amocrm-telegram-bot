<?php

declare(strict_types=1);

namespace Chat;

use Chat\Repository\ConversationRepository;
use Chat\Repository\ExternalUserRepository;
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
            'invokables' => [
                ConversationRepository::class => ConversationRepository::class,
                ExternalUserRepository::class => ExternalUserRepository::class,
                MessageRepository::class => MessageRepository::class,
            ],
            'factories' => [],
        ];
    }
}
