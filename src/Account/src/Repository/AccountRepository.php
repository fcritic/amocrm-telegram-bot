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
     * @param int $amoAccountId ID аккаунта
     * @param string $amoJoId ID аккаунта на стороне amojo
     * @return int
     */
    public function firstOrCreateAccount(string $subDomain, int $amoAccountId, string $amoJoId): int
    {
        /** @var Account $account */
        $account = $this->firstOrCreate(
            ['amo_account_id' => $amoAccountId],
            [
                'sub_domain' => $subDomain,
                'amo_account_id' => $amoAccountId,
                'amojo_id' => $amoJoId,
            ]
        );

        return $account->id;
    }

    /**
     * @param int $amoAccountId
     * @return Model|null
     */
    public function getAccountById(int $amoAccountId): ?Account
    {
        return $this->getBy('amo_account_id', $amoAccountId);
    }

    /**
     * @param string $amoJoId amoJoId аккаунта (ID аккаунта на стороне API чатов)
     * @return string|null
     */
    public function getTelegramToken(string $amoJoId): ?string
    {
        return $this->query()
            ->with('telegramConnection')
            ->where('amojo_id', $amoJoId)
            ->first()
            ?->telegramConnection
            ?->token_bot;
    }

    /**
     * @param string $subDomain
     * @return array<string, int> возвращает локальный id моделей из таблиц
     */
    public function getFieldsId(string $subDomain): array
    {
        /** @var Account $account */
        $account = $this->query()
            ->with('accessToken:id,account_id')
            ->where('sub_domain', $subDomain)
            ->first(['id']);

        return [
            'field_access_token_id' => $account?->getAttribute('accessToken')?->first()?->id,
            'field_account_id' => $account?->id,
        ];
    }

    public function getAccountAndTokens(int $amoAccountId): ?Account
    {
        return $this->query()
            ->with('accessToken')
            ->where('amo_account_id', $amoAccountId)
            ->first();
    }
}
