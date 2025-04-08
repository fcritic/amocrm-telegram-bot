<?php

declare(strict_types=1);

namespace Integration\Service;

use AmoCRM\Model\Account;
use AmoCRM\Repository\Interface\AccountRepositoryInterface;
use App\Exception\InvalidConversationOwnerException;
use App\Exception\InvalidTokenOwnerException;
use Illuminate\Database\Capsule\Manager as Capsule;
use Integration\DTO\MessageDataInterface;
use Integration\Model\Conversation;
use Integration\Model\ExternalUser;
use Integration\Repository\Interface\ConversationRepositoryInterface;
use Integration\Repository\Interface\ExternalUserRepositoryInterface;
use Integration\Repository\Interface\MessageRepositoryInterface;
use RuntimeException;
use Telegram\Model\TelegramConnection;
use Telegram\Repository\Interface\TelegramConnectionRepositoryInterface;
use Telegram\Service\TelegramBotService;
use Throwable;

readonly class DatabaseService
{
    public function __construct(
        protected AccountRepositoryInterface $accountRepo,
        protected ConversationRepositoryInterface $conversationRepo,
        protected ExternalUserRepositoryInterface $externalUserRepo,
        protected MessageRepositoryInterface $messageRepo,
        protected TelegramConnectionRepositoryInterface $telegramRepo,
    ) {
    }

    /**
     * @throws InvalidConversationOwnerException
     * @throws Throwable
     */
    public function saveDataMessage(MessageDataInterface $dtoDb): void
    {
        Capsule::connection()->transaction(function () use ($dtoDb) {

            $accountId = $this->getByIdentifier(identifier: $dtoDb->getAccountIdentifier());

            if ($accountId === null) {
                throw new RuntimeException('Account not found');
            }

            if ($dtoDb->getAmoUserId() === null) {
                $this->updateMessage(dtoDb: $dtoDb);
                return;
            }

            $externalUser = $this->saveExternalUser(accountId: $accountId, dtoDb: $dtoDb);
            $conversation = $this->saveConversation(externalUser: $externalUser, dtoDb: $dtoDb);

            $this->saveMessage(conversation: $conversation, dtoDb: $dtoDb);
        });
    }

    protected function saveExternalUser(int $accountId, MessageDataInterface $dtoDb): ExternalUser
    {
        /** @var ExternalUser */
        return $this->externalUserRepo->firstOrCreateExternalUser(
            accountId: $accountId,
            amoUserId: $dtoDb->getAmoUserId(),
            telegramUserId: (int) $dtoDb->getExternalUserId(),
            username: $dtoDb->getExternalUserUsername(),
            name: $dtoDb->getExternalUserName(),
            phone: $dtoDb->getExternalUserPhone(),
            avatar: $dtoDb->getExternalUserAvatar(),
            profileLink: $dtoDb->getExternalUserProfileLink(),
        );
    }

    /**
     * @throws InvalidConversationOwnerException
     */
    protected function saveConversation(ExternalUser $externalUser, MessageDataInterface $dtoDb): Conversation
    {
        /** @var Conversation $conversation */
        $conversation = $this->conversationRepo->getConversationByTelegramId($dtoDb->getExternalChatId());

        if ($conversation === null || $conversation->external_user_id === $externalUser->id) {
            /** @var Conversation */
            return $this->conversationRepo->updateOrCreateConversation(
                externalUserId: $externalUser->id,
                telegramChatId: (int) $dtoDb->getExternalChatId(),
                amoChatId: $dtoDb->getAmoChatId(),
            );
        }

        throw new InvalidConversationOwnerException(
            "The conversation {$conversation->amo_chat_id} 
            does not belong to an external user {$externalUser->amo_user_id}"
        );
    }

    protected function saveMessage(Conversation $conversation, MessageDataInterface $dtoDb): void
    {
        $this->messageRepo->createMessage(
            conversationId: $conversation->id,
            amoMessageId: $dtoDb->getAmoMessageId(),
            telegramMessageId: (int) $dtoDb->getExternalMessageId(),
            type: $dtoDb->getMessageType(),
            content: $dtoDb->getMessageContent(),
            media: $dtoDb->getMedia(),
            fileName: $dtoDb->getFileName(),
            fileSize: $dtoDb->getFileSize(),
        );
    }

    protected function updateMessage(MessageDataInterface $dtoDb): void
    {
        $this->messageRepo->updateMessage(
            telegramMessageId: (int) $dtoDb->getExternalMessageId(),
            type: $dtoDb->getMessageType(),
            content: $dtoDb->getMessageContent(),
            media: $dtoDb->getMedia(),
            fileName: $dtoDb->getFileName(),
            fileSize: $dtoDb->getFileSize(),
        );
    }

    /**
     * @throws InvalidTokenOwnerException
     */
    public function saveTelegramToken(string $token, int $accountId, string $username): void
    {
        /** @var TelegramConnection|null $telegram */
        $telegram = $this->telegramRepo->getByToken($token);

        /** TODO надо одним запросом получать */
        /** @var Account $account */
        $account = $this->accountRepo->getAccountById($accountId);

        // Проверяем владельца существующего токена
        if (($telegram !== null) && $telegram->account_id !== $account->id) {
            throw new InvalidTokenOwnerException();
        }

        $this->telegramRepo->updateOrCreateTelegram(
            accountId: $account->id,
            botToken: $token,
            webhookSecret: TelegramBotService::generateSecretToken($token),
            usernameBot: $username,
        );
    }

    protected function getByIdentifier(array $identifier): ?int
    {
        /** @var TelegramConnection|Account $model */
        $model = match ($identifier['type']) {
            'amojo_id'       => $this->accountRepo->getBy($identifier['type'], $identifier['value']),
            'webhook_secret' => $this->telegramRepo->getSecret($identifier['value']),
            default          => null
        };

        return match (true) {
            $model instanceof Account => $model->id,
            $model instanceof TelegramConnection => $model->account_id,
            default => null,
        };
    }
}
