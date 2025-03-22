<?php

declare(strict_types=1);

namespace Telegram\Repository;

use App\Repository\AbstractRepository;
use Telegram\Model\TelegramConnection;
use Telegram\Repository\Interface\TelegramConnectionRepositoryInterface;

/**
 * Репозиторий телеграмм бота
 */
class TelegramConnectionRepository extends AbstractRepository implements TelegramConnectionRepositoryInterface
{
    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return TelegramConnection::class;
    }

    /**
     * Добавления сущности токена тг бота в БД
     *
     * @param int $accountId ID пользователя
     * @param string $botToken Токен тг бота
     * @param string $webhookSecret Секретный ключ для хука тг бота
     * @return TelegramConnection
     */
    public function updateOrCreateTelegram(int $accountId, string $botToken, string $webhookSecret): TelegramConnection
    {
        /** @var TelegramConnection */
        return $this->updateOrCreate(
            ['account_id' => $accountId],
            [
                'account_id' => $accountId,
                'token_bot' => $botToken,
                'webhook_secret' => $webhookSecret,
            ]
        );
    }

    public function getByToken(string $token): ?TelegramConnection
    {
        /** @var TelegramConnection */
        return $this->getBy('token_bot', $token);
    }

    public function getBySecret(string $webhookSecret): ?TelegramConnection
    {
        /** @var TelegramConnection */
        return $this->getBy('webhook_secret', $webhookSecret);
    }
}
