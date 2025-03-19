<?php

declare(strict_types=1);

namespace Integration\Service;

use Account\Repository\Interface\AccountRepositoryInterface;
use App\Enum\ResponseMessage;
use App\Enum\ResponseStatus;
use App\Exception\InvalidTokenOwnerException;
use Chat\Repository\Interface\ConversationRepositoryInterface;
use Chat\Repository\Interface\ExternalUserRepositoryInterface;
use Chat\Repository\Interface\MessageRepositoryInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Account\Model\Account;
use Chat\Model\Conversation;
use Chat\Model\ExternalUser;
use Integration\DTO\MessageDataInterface;
use RuntimeException;
use App\Exception\InvalidConversationOwnerException;
use Telegram\Model\Telegram;
use Telegram\Repository\Interface\TelegramRepositoryInterface;
use Telegram\Service\TelegramSettingsService;
use Throwable;

readonly class DatabaseService
{
    public function __construct(
        protected AccountRepositoryInterface $accountRepo,
        protected ConversationRepositoryInterface $conversationRepo,
        protected ExternalUserRepositoryInterface $externalUserRepo,
        protected MessageRepositoryInterface $messageRepo,
        protected TelegramRepositoryInterface $telegramRepo,
    ) {
    }

    /**
     * @throws InvalidConversationOwnerException
     * @throws Throwable
     */
    public function saveDataMessage(MessageDataInterface $dtoDb): void
    {
        Capsule::connection()->transaction(function () use ($dtoDb) {
            /** @var Account $account */
            $account = $this->accountRepo->getByIdentifier(identifier: $dtoDb->getAccountIdentifier());

            if ($account === null) {
                throw new RuntimeException('Account not found');
            }

            $externalUser = $this->saveExternalUser(account: $account, dtoDb: $dtoDb);
            $user = $this->saveUser(account: $account, dtoDb: $dtoDb);
            $conversation = $this->saveConversation(externalUser: $externalUser, dtoDb: $dtoDb);
            $this->saveMessage(externalUser: $externalUser, user: $user, conversation: $conversation, dtoDb: $dtoDb);
        });
    }

    protected function saveUser(Account $account, MessageDataInterface $dtoDb): ExternalUser
    {
        /** @var ExternalUser */
        return $this->externalUserRepo->firstOrCreateExternalUser(
            accountId: $account->id,
            amocrmUid: $dtoDb->getSenderRefId(),
            name: $dtoDb->getSenderName(),
        );
    }

    protected function saveExternalUser(Account $account, MessageDataInterface $dtoDb): ExternalUser
    {
        /** @var ExternalUser */
        return $this->externalUserRepo->firstOrCreateExternalUser(
            accountId: $account->id,
            amocrmUid: $dtoDb->getReceiverRefId(),
            telegramId: $dtoDb->getReceiverId(),
            name: $dtoDb->getReceiverName(),
            number: $dtoDb->getPhone(),
        );
    }

    /**
     * @throws InvalidConversationOwnerException
     */
    protected function saveConversation(ExternalUser $externalUser, MessageDataInterface $dtoDb): Conversation
    {
        /** @var Conversation $conversation */
        $conversation = $this->conversationRepo->getConversationById($dtoDb->getConversationRefId());

        if ($conversation === null || $conversation->external_user_id === $externalUser->id) {
            /** @var Conversation */
            return $this->conversationRepo->updateOrCreateConversation(
                externalUserId: $externalUser->id,
                telegramChatId: (int) $dtoDb->getConversationId(),
                amocrmChatId: $dtoDb->getConversationRefId(),
            );
        }

        throw new InvalidConversationOwnerException(
            "The conversation {$conversation->amocrm_chat_id} 
            does not belong to an external user {$externalUser->amocrm_uid}"
        );
    }

    protected function saveMessage(
        ExternalUser $externalUser,
        ExternalUser $user,
        Conversation $conversation,
        MessageDataInterface $dtoDb
    ): void {
        $this->messageRepo->createMessage(
            conversationId: $conversation->id,
            amocrmMsgId: $dtoDb->getMessageRefId(),
            telegramMsgId: $dtoDb->getMessageId(),
            senderId: $user->id,
            receiverId: $externalUser->id,
            type: $dtoDb->getMessageType(),
            text: $dtoDb->getMessageText(),
            media: $dtoDb->getMedia(),
            fileName: $dtoDb->getFileName(),
            fileSize: (int) $dtoDb->getFileSize(),
        );
    }

    /**
     * @throws InvalidTokenOwnerException
     */
    public function saveTelegramToken(string $token, string $accountId): void
    {
        /** @var Telegram|null $telegram */
        $telegram = $this->telegramRepo->getByToken($token);

        /** @var Account $account */
        $account = $this->accountRepo->getAccountById((int) $accountId);

        // Проверяем владельца существующего токена
        if (($telegram !== null) && $telegram->account_id !== $account->id) {
            throw new InvalidTokenOwnerException();
        }
        $this->telegramRepo->updateOrCreateTelegram(
            accountId: $account->id,
            botToken: $token,
            secretToken: TelegramSettingsService::generateSecretToken($token)
        );
    }
}
