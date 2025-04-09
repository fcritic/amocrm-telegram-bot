<?php

declare(strict_types=1);

namespace App\Database;

use Dot\DependencyInjection\Attribute\Inject;
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseBootstrapper implements BootstrapperInterface
{
    /** @var bool флаг для проверки */
    private static bool $isBootstrapped = false;

    /**
     * @param array $configDatabase
     */
    #[Inject('config.database')]
    public function __construct(protected readonly array $configDatabase)
    {
    }

    /**
     * @return void
     */
    public function bootstrap(): void
    {
        if (self::$isBootstrapped) {
            return;
        }

        $capsule = new Capsule();
        $capsule->addConnection($this->configDatabase);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        self::$isBootstrapped = true;
    }
}
