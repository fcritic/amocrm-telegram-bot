<?php

declare(strict_types=1);

namespace Telegram\Middleware;

use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Telegram\Service\TelegramBotService;

/**
 * Middleware валидирует хук при отправках сообщения из ТГ бота
 */
readonly class TelegramWebhookMiddleware implements MiddlewareInterface
{
    public function __construct(protected TelegramBotService $botService)
    {
    }

    /**
     * @param ServerRequestInterface  $request Запрос
     * @param RequestHandlerInterface $handler TelegramWebhookHandler
     * @return ResponseInterface ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            if (! $this->botService->isValidWebhook($request->getHeaderLine('X-Telegram-Bot-Api-Secret-Token'))) {
                return new Response(ResponseMessage::INVALID_REQUEST);
            }
        } catch (Exception $e) {
            return new Response(ResponseMessage::INVALID_REQUEST, ResponseStatus::UNAUTHORIZED);
        }

        return $handler->handle($request);
    }
}
