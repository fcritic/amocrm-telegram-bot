<?php

declare(strict_types=1);

namespace AmoCRM\Middleware;

use AmoJo\Enum\HeaderType;
use AmoJo\Exception\InvalidRequestWebHookException;
use AmoJo\Webhook\ValidatorWebHooks;
use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Throwable;

/**
 * Middleware валидирует хук при отправке исходящего сообщения из amoCRM
 */
readonly class AmoJoWebhookMiddleware implements MiddlewareInterface
{
    /**
     * @param string $secret
     */
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
            if (! ValidatorWebHooks::isValid($request, $this->secret) && ! $request->hasHeader(HeaderType::SIGNATURE)) {
                return new Response(ResponseMessage::INVALID_REQUEST, ResponseStatus::BAD_REQUEST);
            }
        } catch (InvalidRequestWebHookException $e) {
            return new JsonResponse(['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $handler->handle($request);
    }
}
