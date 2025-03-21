<?php

declare(strict_types=1);

namespace Chat\Repository\Interface;

use Chat\Model\Conversation;

interface ConversationRepositoryInterface
{
    public function getConversationById(string $conversationRefId): ?Conversation;

    public function createConversation(
        int $externalUserId,
        int $telegramChatId,
        string $amocrmChatId
    ): Conversation;

    public function updateOrCreateConversation(
        int $externalUserId,
        int $telegramChatId,
        string $amocrmChatId
    ): Conversation;

    public function firstOrCreateConversation
    (int $externalUserId,
     int $telegramChatId,
     string $amocrmChatId
    ): Conversation;
}
