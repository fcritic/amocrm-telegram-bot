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
    /** @var Message|MessageReactionUpdated */
    protected Message|MessageReactionUpdated $message;

    /**
     * @param Update $update
     * @param string|null $fileId
     * @param string $webhookSecret
     * @param MessageResponse|null $messageResponse
     */
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

    /**
     * @param array $params
     * @return TelegramMessageData
     */
    public static function create(array $params): TelegramMessageData
    {
        return new self(
            $params['update'],
            $params['file_id'] ?? null,
            $params['webhook_secret'],
            $params['response'] ?? null,
        );
    }

    /**
     * @param MessageResponse $response
     * @return self
     */
    public function withResponse(MessageResponse $response): self
    {
        return new self(
            $this->update,
            $this->fileId,
            $this->webhookSecret,
            $response,
        );
    }

    /**
     * @return array
     */
    public function getAccountIdentifier(): array
    {
        return [
          'type' => 'webhook_secret',
          'value' => $this->webhookSecret,
        ];
    }

    /**
     * @return EventType
     */
    public function getEvent(): EventType
    {
        return match (true) {
            isset($this->update->message) => EventType::SEND_MESSAGE,
            isset($this->update->editedMessage) => EventType::EDIT_MESSAGE,
            isset($this->update->messageReaction) => EventType::REACTION_MESSAGE,
        };
    }

    /**
     * @return string|null
     */
    public function getExternalChatId(): ?string
    {
        return (string) $this->message->chat->id;
    }

    /**
     * @return string|null
     */
    public function getAmoChatId(): ?string
    {
        return $this->messageResponse->getConversationRefId();
    }

    /**
     * @return string|null
     */
    public function getAmoUserId(): ?string
    {
        return $this->messageResponse->getSenderRefId();
    }

    /**
     * @return string|null
     */
    public function getExternalUserId(): ?string
    {
        return (string) $this->message->from->id;
    }

    /**
     * @return string|null
     */
    public function getExternalUserName(): ?string
    {
        return $this->message->from->firstName;
    }

    /**
     * @return string|null
     */
    public function getExternalUserPhone(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getExternalUserUsername(): ?string
    {
        return $this->message->from->username;
    }

    /**
     * @return string|null
     */
    public function getExternalUserAvatar(): ?string
    {
        return $this->fileId;
    }

    /**
     * @return string|null
     */
    public function getExternalUserProfileLink(): ?string
    {
        return 'https://t.me/' . $this->getExternalUserUsername();
    }

    /**
     * @return string|null
     */
    public function getExternalMessageId(): ?string
    {
        return (string) $this->message->messageId;
    }

    /**
     * @return string|null
     */
    public function getAmoMessageId(): ?string
    {
        return $this->messageResponse->getMsgRefId();
    }

    /**
     * @return string|null
     */
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

    /**
     * @return string|null
     */
    public function getMessageContent(): ?string
    {
        return $this->message?->text ?? $this->message?->caption ?? '';
    }

    /**
     * @return string|null
     */
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

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->message->document?->fileName ?? '';
    }

    /**
     * @return int|null
     */
    public function getFileSize(): ?int
    {
        return $this->message->document?->fileSize ?? 0;
    }
}
