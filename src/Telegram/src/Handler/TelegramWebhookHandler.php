<?php

declare(strict_types=1);

namespace Telegram\Handler;

use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Helper\Response;
use Exception;
use Integration\Producer\TelegramQueueProducer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Обработчик принимает вебхук на исходящие сообщения в телеграм бота
 */
readonly class TelegramWebhookHandler implements RequestHandlerInterface
{
    /**
     * @param TelegramQueueProducer $producer
     */
    public function __construct(protected TelegramQueueProducer $producer)
    {
    }

    /**
     * Handles the incoming request and
     * returns a JSON response contacts list
     *
     * @throws Exception
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->producer->produce(data: [
                'webhook_secret' => $request->getHeaderLine('X-Telegram-Bot-Api-Secret-Token'),
                'body' => $request->getParsedBody(),
            ]);
        } catch (Exception $e) {
            return new Response($e->getMessage());
        }

        return new Response(ResponseMessage::SUCCESS, ResponseStatus::SUCCESS);
    }
}
