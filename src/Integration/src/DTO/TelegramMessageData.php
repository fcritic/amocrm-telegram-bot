<?php

declare(strict_types=1);

namespace Integration\DTO;

use AmoJo\Enum\MessageType;
use Vjik\TelegramBot\Api\Type\Update\Update;

readonly class TelegramMessageData implements MessageDataInterface
{
    public function __construct(
        protected Update $event,
        protected string $secret
    ) {
    }

    public function getAccountIdentifier(): array
    {
        return [
          'type' => 'secret_token',
          'value' => $this->secret,
        ];
    }

    public function getConversationId(): ?string
    {
        return (string) $this->event?->message->chat->id;
    }

    public function getConversationRefId(): ?string
    {
        return null;
    }

    public function getReceiverId(): ?string
    {
        return null;
    }

    public function getReceiverRefId(): ?string
    {
        return null;
    }

    public function getSenderId(): ?string
    {
        return (string) $this->event?->message->from->id;
    }

    public function getSenderRefId(): ?string
    {
        return null;
    }

    public function getReceiverName(): ?string
    {
        return null;
    }

    public function getSenderName(): ?string
    {
        return $this->event?->message->from->firstName;
    }

    public function getPhone(): ?string
    {
        return null;
    }

    public function getUsername(): ?string
    {
        return $this->event?->message->from->username;
    }

    /** TODO нужно возвращать аватар */
    public function getAvatar(): ?string
    {
        return null;
    }

    public function getProfileLink(): ?string
    {
        return 'https://t.me/' . $this->getUsername();
    }

    public function getMessageId(): ?string
    {
        return (string) $this->event?->message->messageId;
    }

    public function getMessageRefId(): ?string
    {
        return null;
    }

    public function getMessageType(): ?string
    {
        $message = $this->event?->message;

        return match (true) {
            $message->document !== null => MessageType::FILE,
            $message->voice !== null => MessageType::VOICE,
            $message->sticker !== null => MessageType::STICKER,
            $message->location !== null => MessageType::LOCATION,
            $message->photo !== null => MessageType::PICTURE,
            $message->videoNote !== null => MessageType::VIDEO,
            $message->contact !== null => MessageType::CONTACT,
            default => MessageType::TEXT,
        };
    }

    public function getMessageText(): ?string
    {
        return $this->event?->message->text;
    }

    public function getMedia(): ?string
    {
        return $this->event?->message->document->fileId;
    }

    public function getFileName(): ?string
    {
        return $this->event?->message->document->fileName;
    }

    public function getFileSize(): ?string
    {
        return $this->event?->message->document->fileSize;
    }
}
