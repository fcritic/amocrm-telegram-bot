<?php

declare(strict_types=1);

namespace Telegram\Repository\Interface;

use Illuminate\Database\Eloquent\Model;

interface TelegramRepositoryInterface
{
    public function getBySecret(string $secretToken): ?string;

    public function updateOrCreateTelegram(int $accountId, string $botToken, string $secretToken): Model;

    public function getByToken(string $token): ?Model;

    public function getAvatarBotToken(string $fileId): ?string;

    public function getMediaBotToken(string $fileId): ?string;
}
