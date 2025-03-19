<?php

declare(strict_types=1);

namespace Account\Repository\Interface;

use Illuminate\Database\Eloquent\Model;

interface AccountRepositoryInterface
{
    public function firstOrCreateAccount(string $subDomain, int $accountId, string $accountUid): int;

    public function getAccountById(int $accountId): ?Model;

    public function getTelegramToken(string $accountUid): ?string;

    public function getAccessToken(string $accountId): ?Model;

    public function getByIdentifier(array $identifier): ?Model;

    public function getFieldsId(string $subDomain): ?Model;

    public function getBySecret(string $secret): ?Model;
}
