<?php

declare(strict_types=1);

namespace Chat\Repository\Interface;

use Illuminate\Database\Eloquent\Model;

interface ExternalUserRepositoryInterface
{
    public function createExternalUser(
        int $accountId,
        string $amocrmUid,
        string $telegramId = null,
        string|null $username = null,
        string|null $name = null,
        string|null $number = null,
        string|null $avatar = null,
        string|null $profileLink = null
    ): int;

    public function firstOrCreateExternalUser(
        int $accountId,
        string $amocrmUid,
        string $telegramId = null,
        string|null $username = null,
        string|null $name = null,
        string|null $number = null,
        string|null $avatar = null,
        string|null $profileLink = null
    ): Model;
}
