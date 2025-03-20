<?php

declare(strict_types=1);

namespace Telegram\Repository\Interface;

use Telegram\Model\Telegram;

interface TelegramRepositoryInterface
{
    public function getBySecret(string $secretToken): ?Telegram;

    public function updateOrCreateTelegram(int $accountId, string $botToken, string $secretToken): Telegram;

    public function getByToken(string $token): ?Telegram;

    public function getAvatarBotToken(string $fileId): ?string;

    public function getMediaBotToken(string $fileId): ?string;
}
