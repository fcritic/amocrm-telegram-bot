<?php

declare(strict_types=1);

namespace Account\Repository\Interface;

use League\OAuth2\Client\Token\AccessTokenInterface;

interface AccessTokenRepositoryInterface
{
    public function createAccessToken(int $accountId, AccessTokenInterface $accessToken): int;

    public function updateAccessToken(int $id, AccessTokenInterface $accessToken): bool;
}
