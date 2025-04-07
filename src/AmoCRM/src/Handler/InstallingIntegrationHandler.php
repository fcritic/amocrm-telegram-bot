<?php

declare(strict_types=1);

namespace AmoCRM\Handler;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Service\OAuthService;
use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Обработчик принимает вебхук об установки интеграции с кодом авторизации
 */
readonly class InstallingIntegrationHandler implements RequestHandlerInterface
{
    /**
     * @param OAuthService $oAuthServices
     */
    public function __construct(protected OAuthService $oAuthServices)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @psalm-var array<string, string> $params */
        $params = $request->getQueryParams();

        if (! isset($params['code'], $params['referer'])) {
            return new Response(ResponseMessage::INVALID_AUTHORIZATION_CODE);
        }

        try {
            /** Отдает параметры хука об установки для получения пары токенов */
            $this->oAuthServices->process(params: $params);
        } catch (AmoCRMApiException $e) {
            return new Response($e->getMessage(), ResponseStatus::BAD_REQUEST);
        }

        return new Response(ResponseMessage::SUCCESS, ResponseStatus::SUCCESS);
    }
}
