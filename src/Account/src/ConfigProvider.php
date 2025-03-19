<?php

declare(strict_types=1);

namespace Account;

use Account\Repository\AccessTokenRepository;
use Account\Repository\AccountRepository;

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
            'invokables' => [
                AccountRepository::class => AccountRepository::class,
                AccessTokenRepository::class => AccessTokenRepository::class,
            ],
            'factories' => [],
        ];
    }
}
