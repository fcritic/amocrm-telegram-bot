<?php

declare(strict_types=1);

namespace Account\Repository;

use Account\Model\AccessToken;
use Account\Repository\Interface\AccessTokenRepositoryInterface;
use App\Repository\AbstractRepository;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Репозиторий для токена
 */
class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return AccessToken::class;
    }

    /**
     * Создание модели токена доступа в БД
     *
     * @param int $accountId
     * @param AccessTokenInterface $accessToken Объект токена
     * @return int
     */
    public function createAccessToken(int $accountId, AccessTokenInterface $accessToken): int
    {
        /** @var AccessToken $query */
        $query = $this->create([
            'account_id' => $accountId,
            'access_token' => $accessToken->getToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'expires' => $accessToken->getExpires(),
        ]);

        return $query->id;
    }

    /**
     * Редактирование модель токена в БД
     *
     * @param int $id локальный id в базе
     * @param AccessTokenInterface $accessToken объект токена
     * @return bool
     */
    public function updateAccessToken(int $id, AccessTokenInterface $accessToken): bool
    {
        return $this->update([
            'access_token' => $accessToken->getToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'expires' => $accessToken->getExpires(),
        ], $id);
    }
}
