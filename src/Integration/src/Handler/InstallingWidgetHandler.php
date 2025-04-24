<?php

declare(strict_types=1);

namespace Integration\Handler;

use AmoCRM\Models\DisposableTokenModel;
use AmoCRM\Service\AmoCrmClientService;
use AmoCRM\Service\AmoJoClientService;
use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Exception;
use Integration\Service\DatabaseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Telegram\Service\TelegramBotService;

/**
 * Хендлер для установки виджета в аккаунте. Получает запрос при сохранении настроек интеграции
 */
readonly class InstallingWidgetHandler implements RequestHandlerInterface
{
    public function __construct(
        protected TelegramBotService $botService,
        protected DatabaseService $dbService,
        protected AmoCrmClientService $amoCrmClientService,
        protected AmoJoClientService $amoJoClientService
    ) {
    }

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var DisposableTokenModel $disposableToken */
        $disposableToken = $request->getAttribute('disposable_token');
        $token = $request->getParsedBody()['telegram_token'];
        $accountId = $disposableToken->getAccountId();

        try {
            if ($bot = $this->botService->setWebhook($token)) {
                $this->dbService->saveTelegramToken($token, $accountId, $bot->username);

                $this->amoCrmClientService->addSources(
                    $accountId,
                    $disposableToken->getAccountDomain(),
                    $bot
                );

                $this->amoJoClientService->connectChannel($request->getParsedBody()['account_uid']);
            }
        } catch (Exception $e) {
            return new Response(
                ResponseMessage::CHECK_TOKEN,
                ResponseStatus::BAD_REQUEST,
                $e->getMessage()
            );
        }

        return new Response(
            message: ResponseMessage::SUCCESS,
            code: ResponseStatus::SUCCESS,
            data: [
                'external_id' => $bot->username,
                'name' => $bot->firstName,
            ]
        );
    }
}
