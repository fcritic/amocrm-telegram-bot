<?php

declare(strict_types=1);

namespace AmoCRM\Repository;

use AmoCRM\Model\Account;
use AmoCRM\Repository\Interface\AccountRepositoryInterface;
use App\Repository\AbstractRepository;
use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Репозиторий для аккаунта
 */
class AccountRepository extends AbstractRepository implements AccountRepositoryInterface
{
    /** @var int */
    public const CHUNK_SIZE = 250;

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
     * Получение модели аккаунта по id аккаунта
     *
     * @param int $amoAccountId
     * @return Model|null
     */
    public function getAccountById(int $amoAccountId): ?Account
    {
        return $this->getBy('amo_account_id', $amoAccountId);
    }

    /**
     * Получение токена телеграмм по amojo_id аккаунта
     *
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
     * Получение локальных id записей из таблиц access_token и account
     *
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

    /**
     * Получение аккаунта и его токена по id аккаунта.
     * Данный запрос выполняется только в случае валидации пользователя по токену отправленный из-под фронтенда amoCRM
     * @link https://www.amocrm.ru/developers/content/oauth/disposable-tokens
     *
     * @param int $amoAccountId
     * @return Account|null
     */
    public function getAccountWithTokens(int $amoAccountId): ?Account
    {
        /** @var Account */
        return $this->query()
            ->with('accessToken')
            ->where('amo_account_id', $amoAccountId)
            ->first();
    }

    /**
     * Получения всех аккаунтов и их токенов
     *
     * @param int $day
     * @param Closure $callback
     */
    public function getAllAccountsWithTokens(int $day, Closure $callback): void
    {
        // Текущее время в Unix-формате
        $currentTime = time();

        $this->query()
            ->whereHas('accessToken', function ($query) use ($currentTime, $day) {
                // Условие: expires + 80 дней <= текущее время
                $query->whereRaw("(`expires` + ?) <= ?", [
                    $day * 86400, // 80 дней в секундах
                    $currentTime
                ]);
            })
            ->with('accessToken')
            ->chunk(self::CHUNK_SIZE, $callback);
    }

    /**
     * Получения тг токена бота по id аккаунта amoCRM
     *
     * @param int $amoAccountId
     * @return string
     */
    public function getTgToken(int $amoAccountId): string
    {
        /** @var $account Account */
        $account = $this->query()
            ->with('TelegramConnection')
            ->where('amo_account_id', $amoAccountId)
            ->first();

        return $account?->getAttribute('TelegramConnection')->first()->token_bot;
    }

    /**
     * Удаляет все данные об аккаунте при отключении интеграции
     *
     * @param int $amoAccountId
     * @return void
     */
    public function deleteAccount(int $amoAccountId): void
    {
        $this->query()
            ->where('amo_account_id', $amoAccountId)
            ->delete();
    }
}
