<?php

declare(strict_types=1);

namespace App\Enum;

enum ResponseStatus: int
{
    case SUCCESS = 200;
    case NO_CONTENT = 204;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
}
