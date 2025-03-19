<?php

declare(strict_types=1);

namespace Integration\Handler;

use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Exception;
use Integration\Service\DatabaseService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Telegram\Service\TelegramSettingsService;

readonly class SettingsIntegrationHandler implements RequestHandlerInterface
{
    public function __construct(
        protected TelegramSettingsService $settings,
        protected DatabaseService $telegramService,
    ) {
    }

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): JsonResponse
    {
        $token = $request->getParsedBody()['telegram_token'];
        $accountId = $request->getParsedBody()['account_id'];

        try {
            if ($this->settings->setWebhook($token)) {
                $this->telegramService->saveTelegramToken($token, $accountId);
            }
        } catch (Exception $e) {
            return new Response(
                ResponseMessage::CHECK_TOKEN,
                ResponseStatus::BAD_REQUEST,
                $e->getMessage()
            );
        }
        return new Response(ResponseMessage::SUCCESS, ResponseStatus::SUCCESS);
    }
}
