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

/**
 * Сервис для комплексной работы с данными в БД:
 * - Сохранение цепочек сообщений (пользователи → диалоги → сообщения)
 * - Управление Telegram-токенами
 * - Транзакционная обработка данных
 */
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
     * Сохраняет полный контекст сообщения в транзакции:
     * 1. Находит аккаунт по идентификатору из DTO
     * 2. Сохраняет/обновляет внешнего пользователя
     * 3. Создает или обновляет диалог с проверкой владельца
     * 4. Сохраняет метаданные сообщения
     *
     * @param MessageDataInterface $dtoDb
     * @return void
     * @throws Throwable
     * @throws InvalidConversationOwnerException Если диалог принадлежит другому пользователю
     * @throws RuntimeException Если аккаунт не найден
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

    /**
     *  Сохраняет/обновляет профиль внешнего пользователя:
     *  - Привязка к аккаунту amoCRM
     *  - Синхронизация данных Telegram
     *
     * @param int $accountId
     * @param MessageDataInterface $dtoDb
     * @return ExternalUser
     */
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
     *  Управление диалогами:
     *  - Создает новый диалог, если не существует
     *  - Обновляет привязку к amoCRM, если диалог принадлежит текущему пользователю
     *  - Блокирует изменение чужого диалога
     *
     * @param ExternalUser $externalUser
     * @param MessageDataInterface $dtoDb
     * @return Conversation
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

    /**
     *  Сохраняет метаданные сообщения:
     *  - Привязка к диалогу
     *  - Ссылки на сообщения в amoCRM и Telegram
     *  - Тип и содержимое сообщения
     *
     * @param Conversation $conversation
     * @param MessageDataInterface $dtoDb
     * @return void
     */
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

    /**
     *  Обновляет существующее сообщение:
     *  - Используется когда AmoCRM user не определен (системные сообщения)
     *
     * @param MessageDataInterface $dtoDb
     * @return void
     */
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
     *  Привязывает Telegram-бот к аккаунту:
     *  - Генерирует секрет для вебхука
     *  - Проверяет права на токен
     *  - Обновляет или создает подключение
     *
     * @param string $token
     * @param int $accountId
     * @param string $username
     * @return void
     * @throws InvalidTokenOwnerException При попытке перехвата чужого токена
     */
    public function saveTelegramToken(string $token, int $accountId, string $username): void
    {
        /** @var TelegramConnection|null $telegram */
        $telegram = $this->telegramRepo->getByToken($token);

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

    /**
     *  Универсальный поиск аккаунта по идентификатору:
     *  - Поддерживает поиск по amojo_id и webhook_secret
     *  - Возвращает ID аккаунта или null
     *
     * @param array $identifier
     * @return int|null
     */
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
