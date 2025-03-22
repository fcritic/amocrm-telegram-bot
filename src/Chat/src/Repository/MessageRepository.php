<?php

declare(strict_types=1);

namespace Chat\Repository;

use Chat\Model\Message;
use App\Repository\AbstractRepository;
use Chat\Repository\Interface\MessageRepositoryInterface;

/**
 * Репозиторий для сообщения
 */
class MessageRepository extends AbstractRepository implements MessageRepositoryInterface
{
    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return Message::class;
    }

    /**
     * Создание модели сообщения в БД
     *
     * @param int $conversationId ID сущности чата
     * @param string|null $amoMessageId `ref_id`: ID на стороне API чатов AmoJoService
     * @param int $telegramMessageId ID сообщения в тг. Выступает как id сообщения на стороне интеграции
     * @param string $type Тип сообщения
     * @param string|null $content Текст
     * @param string|null $media Медиа
     * @param string|null $fileName Имя файла
     * @param int|null $fileSize Размер файла
     * @return int Model
     */
    public function createMessage(
        int $conversationId,
        string|null $amoMessageId,
        int $telegramMessageId,
        string $type,
        string|null $content,
        string|null $media,
        string|null $fileName,
        int|null $fileSize,
    ): int {
        /** @var Message $message */
        $message = $this->create([
            'conversation_id' => $conversationId,
            'amo_message_id' => $amoMessageId,
            'telegram_message_id' => $telegramMessageId,
            'type' => $type,
            'content' => $content,
            'media' => $media,
            'file_name' => $fileName,
            'file_size' => $fileSize,
        ]);

        return $message->id;
    }

    /**
     * Получения токена тг бота по id файла на стороне интеграции
     * @param string $media
     * @return string|null
     */
    public function getTokenByMedia(string $media): ?string
    {
        return $this->query
            ->with(['conversation.externalUser.account.telegramConnection'])
            ->where('media', $media)
            ->first()
            ?->conversation
            ?->externalUser
            ?->account
            ?->telegramConnection
            ?->token_bot;
    }
}
