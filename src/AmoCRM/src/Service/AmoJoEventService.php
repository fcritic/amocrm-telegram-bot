<?php

declare(strict_types=1);

namespace AmoCRM\Service;

use AmoJo\Client\AmoJoClient;
use AmoJo\DTO\CreateChatResponse;
use AmoJo\Models\Conversation;
use AmoJo\Models\Users\Sender;

class AmoJoEventService
{
    public function __construct(
        readonly AmoJoClient $amoJoClient
    ) {
    }

    public function sendEventAmoJo(): void
    {
    }

    public function createChatAmoJo(
        string $accountUid,
        string $chatId,
        string $userId,
        string $name,
        string $profileLink,
        string $avatar,
        string $externalId = null,
    ): CreateChatResponse {
        return $this->amoJoClient->createChat(
            $accountUid,
            (new Conversation())->setId($chatId),
            (new Sender())
                ->setId($userId)
                ->setName($name)
                ->setProfileLink($profileLink)
                ->setAvatar($avatar),
            $externalId
        );
    }
}
