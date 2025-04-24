<?php

declare(strict_types=1);

namespace AmoCRM\Middleware;

use AmoJo\Exception\InvalidRequestWebHookException;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware валидирует вебхук на отключения интеграции
 * по сигнатуре с использованием секретного ключа интеграции
 */
readonly class UninstallingIntegrationMiddleware implements MiddlewareInterface
{
    #[Inject('config.amojo.secret_key')]
    public function __construct(
        protected string $secretKey
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getQueryParams()['signature'];

        if (! isset($params['signature'])) {
            throw new InvalidRequestWebHookException('Signature is required');
        }

        $signature = hash_hmac(
            'sha256',
            sprintf('%s|%s', $params['client_uuid'], $params['account_id']),
            $this->secretKey
        );

        if (! hash_equals($signature, $params['signature'])) {
            throw new InvalidRequestWebHookException('Invalid signature by uninstalling integration');
        }

        return $handler->handle($request);
    }
}
