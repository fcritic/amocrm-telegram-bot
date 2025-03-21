<?php

declare(strict_types=1);

namespace Telegram\Repository;

use App\Repository\AbstractRepository;
use Telegram\Model\Telegram;
use Telegram\Repository\Interface\TelegramRepositoryInterface;

/**
 * Репозиторий телеграмм бота
 */
class TelegramRepository extends AbstractRepository implements TelegramRepositoryInterface
{
    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return Telegram::class;
    }

    /**
     * Добавления сущности токена тг бота в БД
     *
     * @param int $accountId ID пользователя
     * @param string $botToken Токен тг бота
     * @param string $secretToken Секретный ключ для хука тг бота
     * @return int
     */
    public function updateOrCreateTelegram(int $accountId, string $botToken, string $secretToken): Telegram
    {
        /** @var Telegram */
        return $this->updateOrCreate(
            ['account_id' => $accountId],
            [
                'account_id' => $accountId,
                'token_bot' => $botToken,
                'secret_token' => $secretToken,
            ]
        );
    }

    public function getByToken(string $token): ?Telegram
    {
        /** @var Telegram */
        return $this->getBy('token_bot', $token);
    }

    public function getBySecret(string $secretToken): ?Telegram
    {
        /** @var Telegram */
        return $this->getBy('secret_token', $secretToken);
    }
}
