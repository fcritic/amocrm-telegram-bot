<?php

declare(strict_types=1);

namespace App\Enum;

enum ResponseMessage: string
{
    case INVALID_REQUEST = 'Webhook is not defined';
    case ACCOUNT_NOT_FOUND = 'Account not found';
    case CHECK_TOKEN = 'Please check the token and try again';
    case SUCCESS = 'success';
    case FAIL_CREATE_FILE = 'Failed to create temporary file';
    case NO_CONTENT = 'Could not retrieve file content from the URL';
    case INVALID_EVENT = 'Unable to determine the webhook type';
}
