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
          'type' => 'webhook_secret',
          'value' => $this->secret,
        ];
    }

    public function getExternalChatId(): ?string
    {
        return (string) $this->event?->message->chat->id;
    }

    public function getAmoChatId(): ?string
    {
        return null;
    }

    public function getAmoUserId(): ?string
    {
        return null;
    }

    public function getExternalUserId(): ?string
    {
        return (string) $this->event?->message->from->id;
    }

    public function getExternalUserName(): ?string
    {
        return $this->event?->message->from->firstName;
    }

    public function getExternalUserPhone(): ?string
    {
        return null;
    }

    public function getExternalUserUsername(): ?string
    {
        return $this->event?->message->from->username;
    }

    /** TODO нужно возвращать аватар */
    public function getExternalUserAvatar(): ?string
    {
        return null;
    }

    public function getExternalUserProfileLink(): ?string
    {
        return 'https://t.me/' . $this->getExternalUserUsername();
    }

    public function getExternalMessageId(): ?string
    {
        return (string) $this->event?->message->messageId;
    }

    public function getAmoMessageId(): ?string
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

    public function getMessageContent(): ?string
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
