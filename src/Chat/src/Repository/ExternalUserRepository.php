<?php

declare(strict_types=1);

namespace Chat\Repository;

use Chat\Model\ExternalUser;
use App\Repository\AbstractRepository;
use Chat\Repository\Interface\ExternalUserRepositoryInterface;

/**
 * Репозиторий для внешнего пользователя
 */
class ExternalUserRepository extends AbstractRepository implements ExternalUserRepositoryInterface
{
    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return ExternalUser::class;
    }

    /**
     * Создание модели внешнего пользователя в БД
     *
     * @param int $accountId ID аккаунта к которому относится контакт
     * @param string $amoUserId `ref_id`: ID на стороне API чатов AmoJoService
     * @param int|null $telegramUserId ID пользователя в тг. Выступает как id контакта на стороне интеграции
     * @param string|null $username Юзернейм на стороне тг
     * @param string|null $name Имя контакта
     * @param string|null $phone Аватар пользователя
     * @param string|null $avatar
     * @param string|null $profileLink
     * @return int
     */
    public function createExternalUser(
        int $accountId,
        string $amoUserId,
        int $telegramUserId = null,
        string|null $username = null,
        string|null $name = null,
        string|null $phone = null,
        string|null $avatar = null,
        string|null $profileLink = null
    ): int {
        /** @var ExternalUser $externalUser */
        $externalUser = $this->create([
            'account_id' => $accountId,
            'amo_user_id' => $amoUserId,
            'telegram_user_id' => $telegramUserId,
            'username' => $username,
            'name' => $name,
            'phone' => $phone,
            'avatar' => $avatar,
            'profile_link' => $profileLink,
        ]);

        return $externalUser->id;
    }

    /**
     * @param int $accountId
     * @param string $amoUserId
     * @param int|null $telegramUserId
     * @param string|null $username
     * @param string|null $name
     * @param string|null $phone
     * @param string|null $avatar
     * @param string|null $profileLink
     * @return ExternalUser
     */
    public function firstOrCreateExternalUser(
        int $accountId,
        string $amoUserId,
        int $telegramUserId = null,
        string|null $username = null,
        string|null $name = null,
        string|null $phone = null,
        string|null $avatar = null,
        string|null $profileLink = null
    ): ExternalUser {
        /** @var ExternalUser */
        return $this->firstOrCreate(
            ['amo_user_id' => $amoUserId],
            [
                'account_id' => $accountId,
                'amo_user_id' => $amoUserId,
                'telegram_user_id' => $telegramUserId,
                'username' => $username,
                'name' => $name,
                'phone' => $phone,
                'avatar' => $avatar,
                'profile_link' => $profileLink,
            ]
        );
    }

    /**
     * Получения токена тг бота по id файла на стороне интеграции
     * @param string $avatar
     * @return string|null
     */
    public function getTokenByAvatar(string $avatar): ?string
    {
        return $this->query
            ->with(['account.telegramConnection'])
            ->where('avatar', $avatar)
            ->first()
            ?->account
            ?->telegramConnection
            ?->token_bot;
    }
}
