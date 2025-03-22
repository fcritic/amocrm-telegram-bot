<?php

declare(strict_types=1);

namespace Telegram\Repository\Interface;

use Telegram\Model\TelegramConnection;

interface TelegramConnectionRepositoryInterface
{
    public function getBySecret(string $webhookSecret): ?TelegramConnection;

    public function updateOrCreateTelegram(int $accountId, string $botToken, string $webhookSecret): TelegramConnection;

    public function getByToken(string $token): ?TelegramConnection;
}
