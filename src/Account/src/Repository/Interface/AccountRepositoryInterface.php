<?php

declare(strict_types=1);

namespace Account\Repository\Interface;

use Account\Model\Account;

interface AccountRepositoryInterface
{
    public function firstOrCreateAccount(string $subDomain, int $accountId, string $accountUid): int;

    public function getAccountById(int $accountId): ?Account;

    public function getTelegramToken(string $accountUid): ?string;

    public function getFieldsId(string $subDomain): array;
}
