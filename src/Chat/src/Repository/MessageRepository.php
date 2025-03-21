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
     * @param string|null $amocrmMsgId `ref_id`: ID на стороне API чатов AmoJoService
     * @param string $telegramMsgId ID сообщения в тг. Выступает как id сообщения на стороне интеграции
     * @param int $senderId Отправитель(сущность контакта) сообщения в AmoJoService
     * @param int|null $receiverId Получатель(сущность контакта) сообщения в AmoJoService
     * @param string $type Тип сообщения
     * @param string|null $text Текст
     * @param string|null $media Медиа
     * @param string|null $fileName Имя файла
     * @param int|null $fileSize Размер файла
     * @return int Model
     */
    public function createMessage(
        int $conversationId,
        string|null $amocrmMsgId,
        string $telegramMsgId,
        int $senderId,
        int|null $receiverId,
        string $type,
        string|null $text,
        string|null $media,
        string|null $fileName,
        int|null $fileSize,
    ): int {
        /** @var Message $message */
        $message = $this->create([
            'conversation_id' => $conversationId,
            'amocrm_msg_id' => $amocrmMsgId,
            'telegram_msg_id' => $telegramMsgId,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'type' => $type,
            'text' => $text,
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
            ->with(['sender.account.telegram'])
            ->where('media', $media)
            ->first()
            ?->sender
            ?->account
            ?->telegram
            ?->token_bot;
    }
}
