<?php

declare(strict_types=1);

namespace Account\Repository;

use Account\Model\Account;
use Account\Repository\Interface\AccountRepositoryInterface;
use App\Repository\AbstractRepository;
use Illuminate\Database\Eloquent\Model;

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
                'sub_domain'  => $subDomain,
                'account_id'  => $accountId,
                'account_uid' => $accountUid,
                'is_active'   => true,
            ]
        );

        return $account->id;
    }

    /**
     * @param int $accountId
     * @return Model|null
     */
    public function getAccountById(int $accountId): ?Account
    {
        return $this->getBy('account_id', $accountId);
    }

    /**
     * @param string $accountUid amoJoId аккаунта (ID аккаунта на стороне API чатов)
     * @return string|null
     */
    public function getTelegramToken(string $accountUid): ?string
    {
        return $this->query
            ->with('telegram')
            ->where('account_uid', $accountUid)
            ->first()
            ?->telegram
            ?->token_bot;
    }

    /**
     * @param string $subDomain
     * @return Model|null
     */
    public function getFieldsId(string $subDomain): ?Account
    {
        return $this->query
            ->select('access_token.id as fieldTokenId', 'account.id as fieldAccountId')
            ->leftJoin('access_token', 'access_token.account_id', '=', 'account.id')
            ->where('sub_domain', $subDomain)
            ->first();
    }
}
