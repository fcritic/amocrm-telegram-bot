<?php

declare(strict_types=1);

namespace AmoCRM\Middleware;

use AmoJo\Enum\HeaderType;
use AmoJo\Exception\InvalidRequestWebHookException;
use AmoJo\Webhook\ValidatorWebHooks;
use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Throwable;

/**
 * Middleware валидирует вебхук на исходящие сообщения в канал чатов из интерфейса amoCRM
 */
readonly class AmoJoWebhookMiddleware implements MiddlewareInterface
{
    /**
     * @param string $secret
     */
    #[Inject('config.amojo.secret_key')]
    public function __construct(protected string $secret)
    {
    }

    /**
     * @param ServerRequestInterface $request Запрос
     * @param RequestHandlerInterface $handler AmoJoWebhookHandler
     * @return ResponseInterface ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            if (! ValidatorWebHooks::isValid($request, $this->secret) || ! $request->hasHeader(HeaderType::SIGNATURE)) {
                return new Response(ResponseMessage::INVALID_SIGNATURE, ResponseStatus::UNAUTHORIZED);
            }
        } catch (InvalidRequestWebHookException $e) {
            return new Response(ResponseMessage::INVALID_REQUEST);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $handler->handle($request);
    }
}
