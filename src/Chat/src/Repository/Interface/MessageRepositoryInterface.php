<?php

declare(strict_types=1);

namespace Chat\Repository\Interface;

interface MessageRepositoryInterface
{
    public function createMessage(
        int $conversationId,
        string|null $amoMessageId,
        int $telegramMessageId,
        string $type,
        string|null $content,
        string|null $media,
        string|null $fileName,
        int|null $fileSize,
    ): int;

    public function getTokenByMedia(string $media): ?string;
}
