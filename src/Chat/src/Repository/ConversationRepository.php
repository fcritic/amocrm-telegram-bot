<?php

declare(strict_types=1);

namespace Chat\Repository;

use Chat\Model\Conversation;
use App\Repository\AbstractRepository;
use Chat\Repository\Interface\ConversationRepositoryInterface;

/**
 * Репозиторий для чата
 */
class ConversationRepository extends AbstractRepository implements ConversationRepositoryInterface
{
    /**
     * @return string
     */
    public function getModelClass(): string
    {
        return Conversation::class;
    }

    public function getConversationByTelegramId(string $telegramChatId): ?Conversation
    {
        /** @var Conversation */
        return $this->getBy('telegram_chat_id', $telegramChatId);
    }

    /**
     * Создание модели чата в БД
     *
     * @param int $externalUserId ID контакта. Выступает в роле `sender_id` && `receiver_id`
     * @param int $telegramChatId ID чата тг. Выступает в роле ID чата на стороне интеграции AmoJoService
     * @param string $amoChatId `ref_id`: ID на стороне API чатов AmoJoService
     * @return Conversation
     */
    public function createConversation(int $externalUserId, int $telegramChatId, string $amoChatId): Conversation
    {
        /** @var Conversation */
        return $this->create([
            'external_user_id' => $externalUserId,
            'telegram_chat_id' => $telegramChatId,
            'amo_chat_id' => $amoChatId,
        ]);
    }

    /**
     * @param int $externalUserId
     * @param int $telegramChatId
     * @param string $amoChatId
     * @return Conversation
     */
    public function updateOrCreateConversation(
        int $externalUserId,
        int $telegramChatId,
        string $amoChatId
    ): Conversation {
        /** @var Conversation */
        return $this->updateOrCreate(
            ['telegram_chat_id' => $telegramChatId],
            [
                'external_user_id' => $externalUserId,
                'telegram_chat_id' => $telegramChatId,
                'amo_chat_id' => $amoChatId,
            ]
        );
    }

    /**
     * @param int $externalUserId
     * @param int $telegramChatId
     * @param string $amoChatId
     * @return Conversation
     */
    public function firstOrCreateConversation(
        int $externalUserId,
        int $telegramChatId,
        string $amoChatId
    ): Conversation {
        /** @var Conversation */
        return $this->firstOrCreate(
            ['amo_chat_id' => $amoChatId],
            [
                'external_user_id' => $externalUserId,
                'telegram_chat_id' => $telegramChatId,
                'amo_chat_id' => $amoChatId,
            ]
        );
    }
}
