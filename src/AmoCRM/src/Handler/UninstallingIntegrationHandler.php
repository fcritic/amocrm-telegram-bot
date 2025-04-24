<?php

declare(strict_types=1);

namespace AmoCRM\Handler;

use AmoCRM\Repository\Interface\AccountRepositoryInterface;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Telegram\Service\TelegramBotService;

/**
 * Обработчик принимает вебхук об отключении интеграции. Хук валидируется с помощью сигнатуры
 */
readonly class UninstallingIntegrationHandler implements RequestHandlerInterface
{
    public function __construct(
        protected AccountRepositoryInterface $accountRepo,
        protected TelegramBotService $tgBot,
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $accountId = (int) $request->getQueryParams()['account_id'];

        try {
            $token = $this->accountRepo->getTgToken($accountId);

            if ($this->tgBot->deleteWebhook($token)) {
                $this->accountRepo->deleteAccount($accountId);
            }
        } catch (\Throwable $e) {
            return new Response($e->getMessage());
        }

        return new Response(
            'Uninstalling integration',
            ResponseStatus::SUCCESS
        );
    }
}
