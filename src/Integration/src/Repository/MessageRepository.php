<?php

declare(strict_types=1);

namespace Integration\Repository;

use App\Repository\AbstractRepository;
use Integration\Model\Message;
use Integration\Repository\Interface\MessageRepositoryInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

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
        string|null $media = null,
        string|null $fileName = null,
        int|null $fileSize = null,
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
     * @param int $telegramMessageId
     * @param string $type
     * @param string|null $content
     * @param string|null $media
     * @param string|null $fileName
     * @param int|null $fileSize
     * @return int
     */
    public function updateMessage(
        int $telegramMessageId,
        string $type,
        string|null $content,
        string|null $media = null,
        string|null $fileName = null,
        int|null $fileSize = null,
    ): int {
        /** @var Message $messageId */
        $messageId = $this->getBy('telegram_message_id', $telegramMessageId);

        if ($messageId === null) {
            throw new NotFoundResourceException('Message not found in database');
        }

        $this->update([
            'telegram_message_id' => $telegramMessageId,
            'type' => $type,
            'content' => $content,
            'media' => $media,
            'file_name' => $fileName,
            'file_size' => $fileSize,
        ], $messageId->id);

        return $messageId->id;
    }

    /**
     * Получения токена тг бота по id файла на стороне интеграции
     * @param string $media
     * @return string|null
     */
    public function getTokenByMedia(string $media): ?string
    {
        return $this->query()
            ->with(['conversation.externalUser.account.telegramConnection'])
            ->where('media', $media)
            ->first()
            ?->conversation
            ?->externalUser
            ?->account
            ?->telegramConnection
            ?->token_bot;
    }

    /**
     * Получения id сообщения на стороне amoJo по id сообщения телеграмма
     *
     * @param int $telegramMessageId
     * @return string|null
     */
    public function getAmoMessageId(int $telegramMessageId): ?string
    {
        /** @var Message $massage */
        $massage = $this->getBy('telegram_message_id', $telegramMessageId);

        return $massage->amo_message_id ?? null;
    }
}
