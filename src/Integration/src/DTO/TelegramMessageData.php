<?php

declare(strict_types=1);

namespace Integration\DTO;

use AmoJo\DTO\MessageResponse;
use AmoJo\Enum\MessageType;
use Integration\Enum\EventType;
use Vjik\TelegramBot\Api\Type\Message;
use Vjik\TelegramBot\Api\Type\MessageReactionUpdated;
use Vjik\TelegramBot\Api\Type\Update\Update;

readonly class TelegramMessageData implements MessageDataInterface
{
    protected Message|MessageReactionUpdated $message;

    public function __construct(
        protected Update $update,
        protected ?string $fileId,
        protected string $webhookSecret,
        protected ?MessageResponse $messageResponse = null,
    ) {
        if ($this->update?->message || $this->update?->editedMessage instanceof Message) {
            $this->message = $this->update?->message ?? $this->update?->editedMessage;
        } else {
            $this->message = $this->update?->messageReaction;
        }
    }

    public static function create(array $params): TelegramMessageData
    {
        return new self(
            $params['update'],
            $params['file_id'] ?? null,
            $params['webhook_secret'],
            $params['response'] ?? null,
        );
    }

    public function withResponse(MessageResponse $response): self
    {
        return new self(
            $this->update,
            $this->fileId,
            $this->webhookSecret,
            $response,
        );
    }

    public function getAccountIdentifier(): array
    {
        return [
          'type' => 'webhook_secret',
          'value' => $this->webhookSecret,
        ];
    }

    public function getEvent(): EventType
    {
        return match (true) {
            isset($this->update->message) => EventType::SEND_MESSAGE,
            isset($this->update->editedMessage) => EventType::EDIT_MESSAGE,
            isset($this->update->messageReaction) => EventType::REACTION_MESSAGE,
        };
    }

    public function getExternalChatId(): ?string
    {
        return (string) $this->message->chat->id;
    }

    public function getAmoChatId(): ?string
    {
        return $this->messageResponse->getConversationRefId();
    }

    public function getAmoUserId(): ?string
    {
        return $this->messageResponse->getSenderRefId();
    }

    public function getExternalUserId(): ?string
    {
        return (string) $this->message->from->id;
    }

    public function getExternalUserName(): ?string
    {
        return $this->message->from->firstName;
    }

    public function getExternalUserPhone(): ?string
    {
        return null;
    }

    public function getExternalUserUsername(): ?string
    {
        return $this->message->from->username;
    }

    public function getExternalUserAvatar(): ?string
    {
        return $this->fileId;
    }

    public function getExternalUserProfileLink(): ?string
    {
        return 'https://t.me/' . $this->getExternalUserUsername();
    }

    public function getExternalMessageId(): ?string
    {
        return (string) $this->message->messageId;
    }

    public function getAmoMessageId(): ?string
    {
        return $this->messageResponse->getMsgRefId();
    }

    public function getMessageType(): ?string
    {
        $message = $this->message;

        if ($message instanceof Message) {
            return match (true) {
                $message->document !== null => MessageType::FILE,
                $message->voice !== null => MessageType::VOICE,

                $message->sticker !== null,
                    $message->videoNote !== null,
                    $message->video !== null => MessageType::VIDEO, //!!!!!!!!!

                $message->location !== null => MessageType::LOCATION,
                $message->photo !== null => MessageType::PICTURE,
                $message->contact !== null => MessageType::CONTACT,
                default => MessageType::TEXT,
            };
        }
        return null;
    }

    public function getMessageContent(): ?string
    {
        return $this->message?->text ?? $this->message?->caption ?? '';
    }

    public function getMedia(): ?string
    {
        $message = $this->message;

        if ($message instanceof Message) {
            return $message->sticker?->fileId
                ?? $message->voice?->fileId
                ?? $message->document?->fileId
                ?? $message->audio?->fileId
                ?? $message->video?->fileId
                ?? $message->videoNote?->fileId
                ?? ($message->photo
                ? (
                    (isset($message->photo[2]) ? $message->photo[2]?->fileId : null)
                    ?? (isset($message->photo[0]) ? $message->photo[0]?->fileId : null)
                )
                : null)
                ?? null;
        }
        return '';
    }

    public function getFileName(): ?string
    {
        return $this->message->document?->fileName ?? '';
    }

    public function getFileSize(): ?int
    {
        return $this->message->document?->fileSize ?? 0;
    }
}
