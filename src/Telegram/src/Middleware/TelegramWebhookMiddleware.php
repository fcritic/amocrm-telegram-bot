<?php

declare(strict_types=1);

namespace Telegram\Middleware;

use App\Enum\ResponseMessage;
use App\Helper\Response;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Telegram\Service\TelegramSettingsService;

/**
 * Middleware валидирует хук при отправках сообщения из ТГ бота
 */
readonly class TelegramWebhookMiddleware implements MiddlewareInterface
{
    public function __construct(protected TelegramSettingsService $settings)
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
            if (! $this->settings->isValidWebhook($request->getHeaderLine('X-TelegramConnection-Bot-Api-Secret-Token'))) {
                return new Response(ResponseMessage::INVALID_REQUEST);
            }
        } catch (Exception $e) {
            return new Response(ResponseMessage::INVALID_REQUEST);
        }

        return $handler->handle($request);
    }
}
