<?php

declare(strict_types=1);

namespace Integration\Middleware;

use AmoCRM\Exceptions\DisposableTokenExpiredException;
use AmoCRM\Exceptions\DisposableTokenInvalidDestinationException;
use AmoCRM\Exceptions\DisposableTokenVerificationFailedException;
use AmoCRM\Factory\AmoCRMApiClientFactory;
use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
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
    public function __construct(protected AmoCRMApiClientFactory $amoCRMClientFactory)
    {
    }

    /**
     * @param ServerRequestInterface $request Запрос
     * @param RequestHandlerInterface $handler SettingsIntegrationHandler
     * @return ResponseInterface ResponseInterface
     * @throws DisposableTokenInvalidDestinationException
     * @throws DisposableTokenExpiredException
     * @throws DisposableTokenVerificationFailedException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $errors = [];
        $body = $request->getParsedBody() ?? [];
        $params = [
            'account_id',
            'account_uid',
            'jwt_token',
            'telegram_token'
        ];

        foreach ($params as $param) {
            if (! array_key_exists($param, $body)) {
                $errors[] = "Missing parameter: $param";
                continue;
            }

            $value = trim($body[$param]);
            if ($value === '') {
                $errors[] = "Parameter '$param' cannot be empty";
            }
        }

        if (! empty($errors)) {
            return new Response(
                'Validation errors: ' . implode(', ', $errors),
                ResponseStatus::BAD_REQUEST
            );
        }

        try {
            $apiClient = $this->amoCRMClientFactory->make();
            $disposableToken = $apiClient->getOAuthClient()->parseDisposableToken($body["jwt_token"]);

            $request = $request->withAttribute('disposable_token', $disposableToken);

            return $handler->handle($request);
        } catch (DisposableTokenExpiredException $e) {
            // Время жизни токена истекло
            throw new DisposableTokenExpiredException();
        } catch (DisposableTokenInvalidDestinationException $e) {
            // Не прошёл проверку на адресата токена
            throw new DisposableTokenInvalidDestinationException();
        } catch (DisposableTokenVerificationFailedException $e) {
            // Токен не прошел проверку подписи
            throw new DisposableTokenVerificationFailedException();
        }
    }
}
