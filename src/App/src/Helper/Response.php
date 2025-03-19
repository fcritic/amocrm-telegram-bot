<?php

declare(strict_types=1);

namespace App\Helper;

use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * Создания ответа
 *
 * Класс CreateResponse
 */
class Response extends JsonResponse
{
    public function __construct(
        protected readonly ResponseMessage | string $message,
        protected readonly ResponseStatus $code = ResponseStatus::BAD_REQUEST,
        protected readonly string|null $error = null
    ) {
        $response = ['message' => $message->value ?? $message, 'code' => $code->value];

        if ($error) {
            $response['error'] = $error;
        }
        parent::__construct($response, $code->value);
    }
}
