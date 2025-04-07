<?php

declare(strict_types=1);

namespace AmoCRM\Factory;

use AmoJo\Models\Interfaces\MessageInterface;
use AmoJo\Models\Messages\MessageFactory as BaseMessageFactory;
use Dot\DependencyInjection\Attribute\Inject;
use Integration\DTO\TelegramMessageData;
use Vjik\TelegramBot\Api\Type\Update\Update;

class MessageFactory extends BaseMessageFactory
{
    #[Inject('config.host')]
    public function __construct(private readonly string $host)
    {
    }

    public function createMessage(Update $event, TelegramMessageData $messageData): MessageInterface
    {
        return $this->create($this->buildData($event, $messageData));
    }

    /**
     * @param Update $update
     * @param TelegramMessageData $messageData
     * @return array
     */
    protected function buildData(Update $update, TelegramMessageData $messageData): array
    {
        $message = $update->message ?? $update->editedMessage ?? $update->messageReaction;

        return array_filter([
            'message' => [
                'type' => $messageData->getMessageType(),
                'client_id' => $messageData->getExternalMessageId(),
                'text' => $messageData->getMessageContent(),
                'media' => $this->buildMediaUrl($messageData),
                'file_size' => $messageData->getFileSize(),
                'file_name' => $messageData->getFileName(),
                'media_duration' => $message?->voice?->duration ?? '',
                'contact' => [
                    'name' => $message?->contact?->firstName ?? '',
                    'phone' => $message?->contact?->phoneNumber ?? '',
                ],
                'location' => [
                    'lon' => $message?->location?->longitude ?? '',
                    'lat' => $message?->location?->latitude ?? '',
                ]
            ],
            'timestamp' => $message?->date->getTimestamp(),
            'msec_timestamp' => $message?->date->getTimestamp() * 1000,
        ]);
    }

    /**
     * @param TelegramMessageData $messageData
     * @return string|null
     */
    protected function buildMediaUrl(TelegramMessageData $messageData): ?string
    {
        if ($media = $messageData->getMedia()) {
            return "{$this->host}/api/files/{$media}?with=file";
        }
        return '';
    }
}
