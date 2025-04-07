<?php

declare(strict_types=1);

namespace AmoCRM\Service;

use AmoCRM\Factory\MessageFactory;
use AmoCRM\Factory\SenderFactory;
use AmoCRM\Service\MessageProcessor\ReactHandler;
use AmoCRM\Service\MessageProcessor\ReplyToHandler;
use AmoJo\Client\AmoJoClient;
use AmoJo\DTO\AbstractResponse;
use AmoJo\DTO\ConnectResponse;
use AmoJo\DTO\MessageResponse;
use AmoJo\DTO\ReactResponse;
use AmoJo\Models\Conversation;
use AmoJo\Models\Interfaces\MessageInterface;
use AmoJo\Models\Payload;
use AmoJo\Models\Users\Sender;
use App\Exception\NotFountAmoJoIdException;
use Chat\Repository\Interface\MessageRepositoryInterface;
use Exception;
use Integration\DTO\TelegramMessageData;
use Integration\Enum\EventType;
use RuntimeException;
use Telegram\Repository\Interface\TelegramConnectionRepositoryInterface;
use Vjik\TelegramBot\Api\Type\Update\Update;

class AmoJoClientService
{
    protected Update $event;
    protected TelegramMessageData $messageData;

    public function __construct(
        protected readonly AmoJoClient $amoJoClient,
        protected readonly TelegramConnectionRepositoryInterface $telegramRepo,
        protected readonly MessageRepositoryInterface $messageRepo,
        protected readonly MessageFactory $messageFactory,
        protected readonly SenderFactory $senderFactory,
        protected readonly ReplyToHandler $replyTo,
    ) {
    }

    /**
     * Основной метод обработки событий
     * @throws NotFountAmoJoIdException|RuntimeException|Exception
     */
    public function sendEventAmoJo(
        Update $event,
        TelegramMessageData $messageData
    ): AbstractResponse {
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
     * Получение данных аккаунта с валидацией
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
     * Фабрика создания Payload
     */
    private function createPayload(string $chatId, Sender $sender, MessageInterface $message): Payload
    {
        return (new Payload())
            ->setConversation((new Conversation())->setId($chatId))
            ->setSender($sender)
            ->setMessage($message);
    }

    /**
     * @throws Exception
     */
    protected function sendMessage(string $amoJoId, string $externalId): MessageResponse
    {
        $payload = $this->createPayload(
            (string) $this->event->message->chat->id,
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

    public function connectChannel(string $amoJoId): ConnectResponse
    {
        return $this->amoJoClient->connect($amoJoId);
    }
}
