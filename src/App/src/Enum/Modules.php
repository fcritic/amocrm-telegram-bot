<?php

declare(strict_types=1);

namespace App\Enum;

enum Modules: string
{
    case AMOCRM = 'amocrm';
    case INTEGRATION = 'integration';
    case TELEGRAM = 'telegram';
}
