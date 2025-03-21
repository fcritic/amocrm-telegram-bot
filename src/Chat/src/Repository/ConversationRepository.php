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

    public function getConversationById(string $conversationRefId): ?Conversation
    {
        /** @var Conversation */
        return $this->getBy('amocrm_chat_id', $conversationRefId);
    }

    /**
     * Создание модели чата в БД
     *
     * @param int $externalUserId ID контакта. Выступает в роле `sender_id` && `receiver_id`
     * @param int $telegramChatId ID чата тг. Выступает в роле ID чата на стороне интеграции AmoJoService
     * @param string $amocrmChatId `ref_id`: ID на стороне API чатов AmoJoService
     * @return Conversation
     */
    public function createConversation(int $externalUserId, int $telegramChatId, string $amocrmChatId): Conversation
    {
        /** @var Conversation */
        return $this->create([
            'external_user_id' => $externalUserId,
            'telegram_chat_id' => $telegramChatId,
            'amocrm_chat_id' => $amocrmChatId,
        ]);
    }

    /**
     * @param int $externalUserId
     * @param int $telegramChatId
     * @param string $amocrmChatId
     * @return Conversation
     */
    public function updateOrCreateConversation(
        int $externalUserId,
        int $telegramChatId,
        string $amocrmChatId
    ): Conversation {
        /** @var Conversation */
        return $this->updateOrCreate(
            ['external_user_id' => $externalUserId],
            [
                'external_user_id' => $externalUserId,
                'telegram_chat_id' => $telegramChatId,
                'amocrm_chat_id' => $amocrmChatId,
            ]
        );
    }

    /**
     * @param int $externalUserId
     * @param int $telegramChatId
     * @param string $amocrmChatId
     * @return Conversation
     */
    public function firstOrCreateConversation(
        int $externalUserId,
        int $telegramChatId,
        string $amocrmChatId
    ): Conversation {
        /** @var Conversation */
        return $this->firstOrCreate(
            ['amocrm_chat_id' => $amocrmChatId],
            [
                'external_user_id' => $externalUserId,
                'telegram_chat_id' => $telegramChatId,
                'amocrm_chat_id' => $amocrmChatId,
            ]
        );
    }
}
