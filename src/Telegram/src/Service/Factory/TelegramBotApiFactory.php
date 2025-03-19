<?php

declare(strict_types=1);

namespace Telegram\Service\Factory;

use Exception;
use Vjik\TelegramBot\Api\TelegramBotApi;

class TelegramBotApiFactory
{
    /**
     * @throws Exception
     */
    public function make(string $token): TelegramBotApi
    {
        return new TelegramBotApi(token: $token);
    }
}
