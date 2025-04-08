<?php

declare(strict_types=1);

namespace Integration\Repository\Interface;

use Integration\Model\ExternalUser;

interface ExternalUserRepositoryInterface
{
    public function createExternalUser(
        int $accountId,
        string $amoUserId,
        int $telegramUserId = null,
        string|null $username = null,
        string|null $name = null,
        string|null $phone = null,
        string|null $avatar = null,
        string|null $profileLink = null
    ): int;

    public function firstOrCreateExternalUser(
        int $accountId,
        string $amoUserId,
        int $telegramUserId = null,
        string|null $username = null,
        string|null $name = null,
        string|null $phone = null,
        string|null $avatar = null,
        string|null $profileLink = null
    ): ExternalUser;

    public function getTokenByAvatar(string $avatar): ?string;
}
