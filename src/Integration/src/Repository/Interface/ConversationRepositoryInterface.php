<?php

declare(strict_types=1);

namespace Integration\Repository\Interface;

use Integration\Model\Conversation;

interface ConversationRepositoryInterface
{
    public function getConversationByTelegramId(string $telegramChatId): ?Conversation;

    public function createConversation(
        int $externalUserId,
        int $telegramChatId,
        string $amoChatId
    ): Conversation;

    public function updateOrCreateConversation(
        int $externalUserId,
        int $telegramChatId,
        string $amoChatId
    ): Conversation;

    public function firstOrCreateConversation(
        int $externalUserId,
        int $telegramChatId,
        string $amoChatId
    ): Conversation;
}
