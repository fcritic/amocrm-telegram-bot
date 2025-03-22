<?php

declare(strict_types=1);

namespace Account\Repository\Interface;

use Account\Model\Account;

interface AccountRepositoryInterface
{
    public function firstOrCreateAccount(string $subDomain, int $amoAccountId, string $amoJoId): int;

    public function getAccountById(int $amoAccountId): ?Account;

    public function getTelegramToken(string $amoJoId): ?string;

    public function getFieldsId(string $subDomain): array;
}
