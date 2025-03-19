<?php

declare(strict_types=1);

namespace Integration\Middleware;

use AmoCRM\Exceptions\DisposableTokenExpiredException;
use AmoCRM\Exceptions\DisposableTokenInvalidDestinationException;
use App\Enum\ResponseMessage;
use App\Helper\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Telegram\Service\TelegramSettingsService;

/**
 * Middleware валидирует хук при отправках токена для ТГ бота
 */
readonly class SettingsIntegrationMiddleware implements MiddlewareInterface
{
    public function __construct(protected TelegramSettingsService $settings)
    {
    }

    /**
     * @param ServerRequestInterface  $request Запрос
     * @param RequestHandlerInterface $handler SettingsIntegrationHandler
     * @return ResponseInterface ResponseInterface
     * @throws DisposableTokenInvalidDestinationException
     * @throws DisposableTokenExpiredException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (empty($request->getParsedBody()['account_id'])) {
            return new Response(ResponseMessage::ACCOUNT_NOT_FOUND);
        }

        if (! $this->settings->isValidSettings($request)) {
            return new Response(ResponseMessage::INVALID_REQUEST);
        }

        return $handler->handle($request);
    }
}
