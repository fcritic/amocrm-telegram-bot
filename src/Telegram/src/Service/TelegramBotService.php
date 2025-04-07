<?php

declare(strict_types=1);

namespace Telegram\Service;

use Account\Repository\Interface\AccountRepositoryInterface;
use AmoJo\Enum\MessageType;
use AmoJo\Enum\WebHookType;
use AmoJo\Webhook\AbstractWebHookEvent;
use AmoJo\Webhook\OutgoingMessageEvent;
use AmoJo\Webhook\ReactionEvent;
use AmoJo\Webhook\TypingEvent;
use App\Exception\NotFountTokenException;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Telegram\Service\Factory\TelegramBotApiFactory;
use Vjik\TelegramBot\Api\FailResult;
use Vjik\TelegramBot\Api\TelegramBotApi;
use Vjik\TelegramBot\Api\Type\Message;
use Vjik\TelegramBot\Api\Type\ReactionTypeEmoji;
use Vjik\TelegramBot\Api\Type\ReplyParameters;

class TelegramService
{
    /** @var TelegramBotApi */
    protected TelegramBotApi $bot;

    public function __construct(
        protected readonly TelegramBotApiFactory $factoryBotApi,
        protected readonly AccountRepositoryInterface $accountRepo
    ) {
    }

    /**
     * @throws Exception
     */
    public function sendEventTelegram(AbstractWebHookEvent $event): Message|null
    {
        $token = $this->accountRepo->getTelegramToken(amoJoId: $event->getAccountUid());

        if ($token === null) {
            throw new NotFountTokenException('Telegram token not found');
        }

        $this->bot = $this->factoryBotApi->make(token: $token);

        match (true) {
            $event instanceof OutgoingMessageEvent => $message = $this->sendMessage($event),
            $event instanceof TypingEvent          => $this->sendTyping($event),
            $event instanceof ReactionEvent        => $this->sendReaction($event),
        };

        return $message ?? null;
    }

    /**
     * @param OutgoingMessageEvent $event
     * @return null
     * @throws RuntimeException
     */
    protected function sendMessage(OutgoingMessageEvent $event): Message|null
    {
        $chatId = $event->getConversation()->getId();
        $replyUid = (int) $event->getReplyTo()?->getReplyUid();
        $replyParams = new ReplyParameters($replyUid);

        $methodMap = [
            MessageType::TEXT => [
                'method' => 'sendMessage',
                'params' => ['text' => $event->getMessage()->getText()],
            ],
            MessageType::VOICE => [
                'method' => 'sendVoice',
                'params' => ['voice' => $event->getMessage()->getMedia()],
            ],
            MessageType::PICTURE => [
                'method' => 'sendSticker',
                'params' => ['sticker' => $event->getMessage()->getMedia()],
            ],
            MessageType::FILE => [
                'method' => 'sendDocument',
                'params' => [
                    'document' => $event->getMessage()->getMedia(),
                    'caption' => $event->getMessage()->getFileName(),
                ],
            ],
            MessageType::VIDEO => [
                'method' => 'sendVideo',
                'params' => ['video' => $event->getMessage()->getMedia()],
            ],
        ];

        $messageType = $event->getMessage()->getType();
        $config = $methodMap[$messageType] ?? throw new InvalidArgumentException('Unsupported message type');

        $args = array_merge(
            [
                'chatId' => $chatId,
                'replyParameters' => $replyParams,
            ],
            $config['params']
        );

        $result = $this->bot->{$config['method']}(...$args);

        if ($result instanceof FailResult) {
            throw new RuntimeException($result->description, $result->response->statusCode);
        }

        return $result ?? null;
    }

    protected function sendReaction(ReactionEvent $event): void
    {
        $this->bot->setMessageReaction(
            chatId: $event->getConversation()->getId(),
            messageId: (int) $event->getMessage()->getUid(),
            reaction: [new ReactionTypeEmoji($event->getEmoji())]
        );
    }

    protected function sendTyping(TypingEvent $event): void
    {
        $this->bot->sendChatAction(
            chatId: $event->getConversation()->getId(),
            action: WebHookType::TYPING
        );
    }
}
