<?php

declare(strict_types=1);

namespace Telegram\Service;

use AmoCRM\Repository\AccountRepository;
use AmoCRM\Repository\Interface\AccountRepositoryInterface;
use AmoJo\Enum\MessageType;
use AmoJo\Enum\WebHookType;
use AmoJo\Webhook\DTO\DtoInterface;
use AmoJo\Webhook\DTO\OutgoingMessageEvent;
use AmoJo\Webhook\DTO\ReactionEvent;
use AmoJo\Webhook\DTO\TypingEvent;
use App\Exception\NotFountTokenException;
use Dot\DependencyInjection\Attribute\Inject;
use Exception;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Telegram\Factory\TelegramBotApiFactory;
use Telegram\Model\TelegramConnection;
use Telegram\Repository\Interface\TelegramConnectionRepositoryInterface;
use Vjik\TelegramBot\Api\FailResult;
use Vjik\TelegramBot\Api\TelegramBotApi;
use Vjik\TelegramBot\Api\Type\Message;
use Vjik\TelegramBot\Api\Type\ReactionTypeEmoji;
use Vjik\TelegramBot\Api\Type\ReplyParameters;
use Vjik\TelegramBot\Api\Type\User;

/**
 * Сервис-обертка для работы с Telegram Bot API:
 * - Настройка вебхуков
 * - Отправка сообщений/реакций
 * - Валидация входящих запросов
 * - Генерация секретных токенов
 */
class TelegramBotService
{
    /** @var TelegramBotApi */
    protected TelegramBotApi $bot;

    /**
     * @param TelegramBotApiFactory $factoryBotApi
     * @param AccountRepositoryInterface $accountRepo
     * @param TelegramConnectionRepositoryInterface $telegramRepo
     * @param array $externalGateway
     */
    #[Inject(
        TelegramBotApiFactory::class,
        AccountRepository::class,
        TelegramConnectionRepositoryInterface::class,
        'config.external_gateway'
    )]
    public function __construct(
        protected readonly TelegramBotApiFactory $factoryBotApi,
        protected readonly AccountRepositoryInterface $accountRepo,
        protected readonly TelegramConnectionRepositoryInterface $telegramRepo,
        protected readonly array $externalGateway
    ) {
    }

    /**
     * Настраивает вебхук для Telegram-бота:
     * - Указывает URL для обработки событий
     * - Разрешает обработку сообщений/редактирований/реакций
     * - Устанавливает секретный токен для верификации запросов
     *
     * @param string $token Токен бота из BotFather
     * @return User|null Информация о боте или null при ошибке
     * @throws Exception При неудачной настройке вебхука
     */
    public function setWebhook(string $token): ?User
    {
        $telegramBot = $this->factoryBotApi->make($token);

        $setWebhook = $telegramBot->setWebhook(
            url: $this->externalGateway['telegram_url'],
            allowUpdates: [
                'message',
                'edited_message',
                'message_reaction',
            ],
            secretToken: static::generateSecretToken($token)
        );

        if ($setWebhook) {
            return $telegramBot->getMe();
        }
        return null;
    }

    /**
     * Отключения вебхука в тг боте
     *
     * @param string $token
     * @return bool
     * @throws Exception
     */
    public function deleteWebhook(string $token): bool
    {
        $telegramBot = $this->factoryBotApi->make($token);

        $result = $telegramBot->deleteWebhook();

        return match (true) {
            $result instanceof FailResult => false,
            default => true,
        };
    }

    /**
     * Генерирует уникальный секретный токен на основе токена бота.
     * Используется для верификации входящих вебхуков.
     *
     * @param string $botToken Токен бота (например: "123456:ABC-DEF1234ghIkl")
     * @return string UUID v5 в формате строки
     */
    public static function generateSecretToken(string $botToken): string
    {
        return Uuid::uuid5(ns: Uuid::NAMESPACE_DNS, name: $botToken)->toString();
    }

    /**
     * Проверяет соответствие секретного токена из заголовка запроса
     * сохраненному значению в базе данных.
     *
     * @param string $secretHeader Значение заголовка X-Telegram-Bot-Api-Secret-Token
     * @return bool true если токен верифицирован
     */
    public function isValidWebhook(string $secretHeader): bool
    {
        /** @var TelegramConnection $secret */
        $secret = $this->telegramRepo->getSecret($secretHeader);

        return hash_equals($secret->webhook_secret, $secretHeader);
    }

    /**
     * Основной метод для отправки событий в Telegram:
     * - Определяет тип события (сообщение/реакция/индикатор)
     * - Перенаправляет обработку в соответствующий метод
     *
     * @param DtoInterface $event Событие для отправки
     * @return Message|null Объект сообщения (для текстовых/медиа-сообщений)
     * @throws NotFountTokenException Если токен бота не найден
     * @throws Exception
     */
    public function sendEventTelegram(DtoInterface $event): Message|null
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
     * Отправляет сообщение в Telegram.
     * Поддерживаемые типы: текст, голос, стикер, документ, видео.
     *
     * @param OutgoingMessageEvent $event Событие с данными сообщения
     * @return Message|null Объект отправленного сообщения
     * @throws RuntimeException При ошибке API Telegram
     * @throws InvalidArgumentException При неподдерживаемом типе сообщения
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

    /**
     * Добавляет реакцию к сообщению.
     *
     * @param ReactionEvent $event Событие с данными реакции:
     *   - emoji: смайл (например, "👍")
     *   - messageUid: ID сообщения в Telegram
     *   - conversation: ID чата
     */
    protected function sendReaction(ReactionEvent $event): void
    {
        $this->bot->setMessageReaction(
            chatId: $event->getConversation()->getId(),
            messageId: (int) $event->getMessage()->getUid(),
            reaction: [new ReactionTypeEmoji($event->getEmoji())]
        );
    }

    /**
     * Активирует индикатор "Печатает..." в указанном чате.
     *
     * @param TypingEvent $event Событие с данными чата
     */
    protected function sendTyping(TypingEvent $event): void
    {
        $this->bot->sendChatAction(
            chatId: $event->getConversation()->getId(),
            action: WebHookType::TYPING
        );
    }
}
