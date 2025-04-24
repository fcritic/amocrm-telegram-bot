<?php

declare(strict_types=1);

namespace AmoCRM\Repository\Interface;

use AmoCRM\Model\Account;
use Closure;

interface AccountRepositoryInterface
{
    public function firstOrCreateAccount(string $subDomain, int $amoAccountId, string $amoJoId): int;

    public function getAccountById(int $amoAccountId): ?Account;

    public function getTelegramToken(string $amoJoId): ?string;

    public function getFieldsId(string $subDomain): array;

    public function getAccountWithTokens(int $amoAccountId): ?Account;

    public function getAllAccountsWithTokens(int $day, Closure $callback): void;

    public function getTgToken(int $amoAccountId): string;

    public function deleteAccount(int $amoAccountId): void;
}
