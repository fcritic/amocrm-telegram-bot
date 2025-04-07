<?php

declare(strict_types=1);

namespace Account;

use Account\Repository\AccessTokenRepository;
use Account\Repository\AccountRepository;
use Account\Repository\Interface\AccessTokenRepositoryInterface;
use Account\Repository\Interface\AccountRepositoryInterface;

/**
 * ConfigProvider class
 */
class ConfigProvider
{
    /** @return array */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /** @return array */
    public function getDependencies(): array
    {
        return [
            'aliases' => [
                AccessTokenRepositoryInterface::class => AccessTokenRepository::class,
                AccountRepositoryInterface::class => AccountRepository::class,
            ],
        ];
    }
}
