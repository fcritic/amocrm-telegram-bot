<?php

declare(strict_types=1);

namespace AmoCRM\Handler;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Service\OAuthService;
use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Обработчик принимает вебхук об установки интеграции с кодом авторизации
 */
readonly class OAuthAmoHandler implements RequestHandlerInterface
{
    /**
     * @param OAuthService $oAuthServices
     */
    public function __construct(
        protected OAuthService $oAuthServices,
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function handle(ServerRequestInterface $request): JsonResponse
    {
        $params = $request->getQueryParams();

        if (! isset($params['code'], $params['referer'])) {
            return new JsonResponse(['message' => 'Invalid webhook authorization code'], 400);
        }

        try {
            $this->oAuthServices->process(params: $params);
        } catch (AmoCRMApiException $e) {
            return new Response($e->getMessage(), ResponseStatus::BAD_REQUEST);
        }

        return new Response(ResponseMessage::SUCCESS, ResponseStatus::SUCCESS);
    }
}
