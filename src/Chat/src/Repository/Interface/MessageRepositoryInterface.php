<?php

declare(strict_types=1);

namespace Chat\Repository\Interface;

interface MessageRepositoryInterface
{
    public function createMessage(
        int $conversationId,
        string|null $amocrmMsgId,
        string $telegramMsgId,
        int $senderId,
        int|null $receiverId,
        string $type,
        string|null $text,
        string|null $media,
        string|null $fileName,
        int|null $fileSize,
    ): int;

    public function getTokenByMedia(string $media): ?string;
}
