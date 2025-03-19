<?php

declare(strict_types=1);

namespace Telegram\Repository;

use App\Repository\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
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
    public function updateOrCreateTelegram(int $accountId, string $botToken, string $secretToken): Model
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

    public function getByToken(string $token): ?Model
    {
        return $this->getBy('token_bot', $token);
    }

    public function getBySecret(string $secretToken): ?string
    {
        /** @var Telegram $telegram */
        $telegram = $this->getBy('secret_token', $secretToken);
        return $telegram->secret_token;
    }

    public function getAvatarBotToken(string $fileId): ?string
    {
        return $this->getBotTokenByFileId($fileId, 'avatar');
    }

    public function getMediaBotToken(string $fileId): ?string
    {
        return $this->getBotTokenByFileId($fileId, 'media');
    }

    protected function getBotTokenByFileId(string $fileId, string $sourceType): ?string
    {
        $query = $this->query()
            ->select('telegram.token_bot')
            ->from('telegram')
            ->join('external_user', 'telegram.account_id', '=', 'external_user.account_id');

        switch ($sourceType) {
            case 'avatar':
                $query->where('external_user.avatar', $fileId);
                break;
            case 'media':
                $query->join('message', 'external_user.id', '=', 'message.sender_id')
                    ->where('message.media', $fileId);
                break;
            default:
                throw new \InvalidArgumentException("Invalid source type: {$sourceType}");
        }

        return $query->value('token_bot');
    }
}
