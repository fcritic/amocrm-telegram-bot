<?php

declare(strict_types=1);

namespace App\Database;

interface BootstrapperInterface
{
    public function bootstrap(): void;
}
