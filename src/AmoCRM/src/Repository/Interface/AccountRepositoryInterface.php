<?php

declare(strict_types=1);

namespace AmoCRM\Repository\Interface;

use AmoCRM\Model\Account;

interface AccountRepositoryInterface
{
    public function firstOrCreateAccount(string $subDomain, int $amoAccountId, string $amoJoId): int;

    public function getAccountById(int $amoAccountId): ?Account;

    public function getTelegramToken(string $amoJoId): ?string;

    public function getFieldsId(string $subDomain): array;

    public function getAccountAndTokens(int $amoAccountId): ?Account;
}
