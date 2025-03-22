<?php

declare(strict_types=1);

namespace Telegram\Repository\Interface;

use Telegram\Model\TelegramConnection;

interface TelegramRepositoryInterface
{
    public function getBySecret(string $secretToken): ?TelegramConnection;

    public function updateOrCreateTelegram(int $accountId, string $botToken, string $secretToken): TelegramConnection;

    public function getByToken(string $token): ?TelegramConnection;
}
