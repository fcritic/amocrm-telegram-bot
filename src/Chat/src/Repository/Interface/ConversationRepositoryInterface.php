<?php

declare(strict_types=1);

namespace Chat\Repository\Interface;

use Illuminate\Database\Eloquent\Model;

interface ConversationRepositoryInterface
{
    public function getConversationById(string $conversationRefId): ?Model;

    public function createConversation(int $externalUserId, int $telegramChatId, string $amocrmChatId): Model;

    public function updateOrCreateConversation(int $externalUserId, int $telegramChatId, string $amocrmChatId): Model;

    public function firstOrCreateConversation(int $externalUserId, int $telegramChatId, string $amocrmChatId): Model;
}
