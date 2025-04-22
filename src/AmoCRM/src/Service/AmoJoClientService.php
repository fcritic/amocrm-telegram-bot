<?php

declare(strict_types=1);

namespace AmoCRM\Service;

use AmoCRM\Factory\MessageFactory;
use AmoCRM\Factory\SenderFactory;
use AmoCRM\Service\MessageProcessor\ReplyToHandler;
use AmoJo\Client\AmoJoClient;
use AmoJo\DTO\AbstractResponse;
use AmoJo\DTO\ConnectResponse;
use AmoJo\DTO\DeliveryResponse;
use AmoJo\DTO\MessageResponse;
use AmoJo\DTO\ReactResponse;
use AmoJo\Models\Conversation;
use AmoJo\Models\Deliver;
use AmoJo\Models\Interfaces\MessageInterface;
use AmoJo\Models\Payload;
use AmoJo\Models\Users\Sender;
use App\Exception\NotFountAmoJoIdException;
use Exception;
use Integration\DTO\TelegramMessageData;
use Integration\Enum\EventType;
use Integration\Repository\Interface\MessageRepositoryInterface;
use Telegram\Repository\Interface\TelegramConnectionRepositoryInterface;
use Vjik\TelegramBot\Api\Type\Update\Update;

/**
 * Сервис-обертка для работы с API чатов amoCRM:
 * - Отправка сообщений, реакций, обновление статусов
 * - Обработка событий Telegram (сообщения, редактирование, реакции)
 * - Управление подключением каналов
 */
class AmoJoClientService
{
    /** @var Update Текущее обрабатываемое событие Telegram */
    protected Update $event;

    /** @var TelegramMessageData DTO с данными сообщения для AmoJo */
    protected TelegramMessageData $messageData;

    public function __construct(
        protected AmoJoClient $amoJoClient,
        protected TelegramConnectionRepositoryInterface $telegramRepo,
        protected MessageRepositoryInterface $messageRepo,
        protected MessageFactory $messageFactory,
        protected SenderFactory $senderFactory,
        protected ReplyToHandler $replyTo,
    ) {
    }

    /**
     * Основной метод обработки событий:
     * - Определяет тип события (SEND_MESSAGE, EDIT_MESSAGE, REACTION_MESSAGE)
     * - Перенаправляет обработку в соответствующий метод
     *
     * @throws NotFountAmoJoIdException Если аккаунт не найден
     * @throws Exception При ошибках API
     */
    public function sendEventAmoJo(Update $event, TelegramMessageData $messageData): AbstractResponse
    {
        $account = $this->getAccountData($messageData->getAccountIdentifier()['value']);

        $this->event = $event;
        $this->messageData = $messageData;

        return match ($messageData->getEvent()) {
            EventType::SEND_MESSAGE => $this->sendMessage($account['amojo_id'], $account['external_id']),
            EventType::EDIT_MESSAGE => $this->sendEditMessage($account['amojo_id']),
            EventType::REACTION_MESSAGE => $this->sendReaction($account['amojo_id']),
        };
    }

    /**
     *  Получает данные аккаунта по секрету вебхука:
     *  - Ищет привязку Telegram-бота к аккаунту amoCRM
     *  - Валидирует наличие AmoJo ID
     *
     * @param string $webhookSecret
     * @return array
     * @throws NotFountAmoJoIdException
     */
    private function getAccountData(string $webhookSecret): array
    {
        $account = $this->telegramRepo->getAmoJoIdAndUsername($webhookSecret);
        $amoJoId = $account?->getAttribute('account')?->amojo_id;
        $username = $account?->username_bot;

        if (empty($amoJoId)) {
            throw new NotFountAmoJoIdException('AmoJoId account not found');
        }

        return [
            'amojo_id' => $amoJoId,
            'external_id' => $username,
        ];
    }

    /**
     *  Создает объект Payload для AmoJo API:
     *  - Устанавливает чат, отправителя и сообщение
     *  - Используется для всех типов сообщений
     *
     * @param string $chatId
     * @param Sender $sender
     * @param MessageInterface $message
     * @return Payload
     */
    private function createPayload(string $chatId, Sender $sender, MessageInterface $message): Payload
    {
        return (new Payload())
            ->setConversation((new Conversation())->setId($chatId))
            ->setSender($sender)
            ->setMessage($message);
    }

    /**
     *  Отправка нового сообщения:
     *  - Создает отправителя через SenderFactory
     *  - Формирует сообщение через MessageFactory
     *  - Обрабатывает replyTo-логику
     *  - Отправляет через AmoJoClient
     *
     * @param string $amoJoId
     * @param string $externalId
     * @return MessageResponse
     * @throws Exception
     */
    protected function sendMessage(string $amoJoId, string $externalId): MessageResponse
    {
        $payload = $this->createPayload(
            sprintf('%s_%s', $this->event->message->chat->id, $externalId),
            $this->senderFactory->create(
                $this->event->message->from,
                $this->messageData->getExternalUserAvatar()
            ),
            $this->messageFactory->createMessage($this->event, $this->messageData)
        );

        $this->replyTo->handle($payload, $this->event);

        return $this->amoJoClient->sendMessage($amoJoId, $payload, $externalId);
    }

    /**
     *  Редактирование существующего сообщения:
     *  - Использует данные из editedMessage
     *  - Не обрабатывает replyTo
     *
     * @param string $amoJoId
     * @return MessageResponse
     * @throws Exception
     */
    private function sendEditMessage(string $amoJoId): MessageResponse
    {
        $payload = $this->createPayload(
            (string) $this->event->editedMessage->chat->id,
            $this->senderFactory->create(
                $this->event->editedMessage->from,
                $this->messageData->getExternalUserAvatar()
            ),
            $this->messageFactory->createMessage($this->event, $this->messageData)
        );

        return $this->amoJoClient->editMessage($amoJoId, $payload);
    }

    /**
     *  Обработка реакций на сообщение:
     *  - Определяет тип реакции (добавление/удаление)
     *  - Получает AmoJo ID сообщения из локальной БД
     *  - Формирует запрос с emoji
     *
     * @param string $amoJoId
     * @return ReactResponse|null
     * @throws Exception
     */
    private function sendReaction(string $amoJoId): ?ReactResponse
    {
        $react = $this->event->messageReaction;
        $message = $this->messageFactory->createMessage($this->event, $this->messageData);
        $amoJoRefId = $this->messageRepo->getAmoMessageId((int) $message->getUid());
        $type = true;

        if (empty($react->newReaction)) {
            $type = false;
            $emoji = $react?->oldReaction[0]?->toRequestArray()['emoji'];
        } else {
            $emoji = $react?->newReaction[0]?->toRequestArray()['emoji'];
        }

        if ($amoJoRefId !== null) {
            $message->setRefUid($amoJoRefId);
        }

        return $this->amoJoClient->react(
            $amoJoId,
            (new Conversation())->setId((string) $react->chat->id),
            $this->senderFactory->create($react->user),
            $message,
            $emoji,
            $type
        );
    }

    /**
     *  Обновление статуса доставки сообщения:
     *  - Используется для подтверждения доставки или ошибок
     *  - Формирует объект Deliver с кодом и описанием ошибки
     *
     * @param string $amoJoId
     * @param string $messageRefId
     * @param int $status
     * @param string $message
     * @param int $errorCode
     * @return DeliveryResponse
     */
    public function updateStatus(
        string $amoJoId,
        string $messageRefId,
        int $status,
        string $message,
        int $errorCode
    ): DeliveryResponse {
        return $this->amoJoClient->deliverStatus(
            $amoJoId,
            $messageRefId,
            (new Deliver($status))
                ->setMessageError($message)
                ->setErrorCode($errorCode)
        );
    }

    /**
     *  Подключение канала к аккаунту
     *
     * @param string $amoJoId
     * @return ConnectResponse
     */
    public function connectChannel(string $amoJoId): ConnectResponse
    {
        return $this->amoJoClient->connect($amoJoId);
    }
}
