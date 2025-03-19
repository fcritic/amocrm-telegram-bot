<?php

declare(strict_types=1);

namespace Account\Repository;

use Account\Model\Account;
use Account\Repository\Interface\AccountRepositoryInterface;
use App\Repository\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Telegram\Model\Telegram;

/**
 * Репозиторий для аккаунта
 */
class AccountRepository extends AbstractRepository implements AccountRepositoryInterface
{
    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return Account::class;
    }

    /**
     * Создание модели аккаунта в БД
     *
     * @param string $subDomain Домен аккаунта
     * @param int $accountId ID аккаунта
     * @param string $accountUid ID аккаунта на стороне amojo
     * @return int
     */
    public function firstOrCreateAccount(string $subDomain, int $accountId, string $accountUid): int
    {
        /** @var Account $account */
        $account = $this->firstOrCreate(
            ['account_id' => $accountId],
            [
                'sub_domain' => $subDomain,
                'account_id' => $accountId,
                'account_uid' => $accountUid,
                'is_active' => true,
            ]
        );

        return $account->id;
    }

    /**
     * @param int $accountId
     * @return Model|null
     */
    public function getAccountById(int $accountId): ?Model
    {
        return $this->getBy('account_id', $accountId);
    }

    /**
     * @param string $accountUid amoJoId аккаунта (ID аккаунта на стороне API чатов)
     * @return string|null
     */
    public function getTelegramToken(string $accountUid): ?string
    {
        $query = $this->query()
            ->select('telegram.token_bot as token_bot')
            ->leftJoin('telegram', 'account.id', '=', 'telegram.account_id')
            ->where('account.account_uid', $accountUid)
            ->first();

        return $query?->value('token_bot');
    }

    /**
     * @param string $accountId Числовое ID аккаунта
     * @return Model|null
     */
    public function getAccessToken(string $accountId): ?Model
    {
        return $this->query()
            ->select('account.account_id', 'access_token.access_token')
            ->leftJoin('access_token', 'account.id', '=', 'access_token.user_id')
            ->where('account.account_id', $accountId)
            ->first();
    }

    /**
     * @param array $identifier
     * @return Model|null
     */
    public function getByIdentifier(array $identifier): ?Model
    {
        return match ($identifier['type']) {
            'account_uid' => $this->getBy($identifier['type'], $identifier['value']),
            'secret' => $this->getBySecret($identifier['value']),
            default => null
        };
    }

    /**
     * @param string $subDomain
     * @return Model|null
     */
    public function getFieldsId(string $subDomain): ?Model
    {
        return $this->query()
            ->select('access_token.id as fieldTokenId', 'account.id as fieldAccountId')
            ->leftJoin('access_token', 'access_token.account_id', '=', 'account.id')
            ->where('sub_domain', $subDomain)
            ->first();
    }

    public function getBySecret(string $secret): ?Model
    {
        return $this->query()
            ->select('*')
            ->leftJoin('telegram', 'telegram.account_id', '=', 'account.id')
            ->where('telegram.secret_token', $secret)
            ->first();
    }
}
