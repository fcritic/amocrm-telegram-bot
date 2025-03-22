<?php

declare(strict_types=1);

namespace Telegram\Service;

use AmoCRM\Exceptions\DisposableTokenExpiredException;
use AmoCRM\Exceptions\DisposableTokenInvalidDestinationException;
use AmoCRM\Exceptions\DisposableTokenVerificationFailedException;
use AmoCRM\Service\Factory\AmoCRMApiClientFactory;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Telegram\Model\TelegramConnection;
use Telegram\Repository\Interface\TelegramConnectionRepositoryInterface;
use Telegram\Service\Factory\TelegramBotApiFactory;
use Vjik\TelegramBot\Api\TelegramBotApi;

class TelegramSettingsService
{
    /** @var TelegramBotApi */
    protected TelegramBotApi $bot;

    public function __construct(
        protected readonly TelegramBotApiFactory $factoryBotApi,
        protected readonly TelegramConnectionRepositoryInterface $telegramRepo,
        protected readonly AmoCRMApiClientFactory $amoCRMClientFactory,
        protected readonly string $urlWebhook
    ) {
    }

    /**
     * Настройка вебхука тг бота
     *
     * @param string $token токен ТГ бота
     * @return bool
     * @throws Exception
     */
    public function setWebhook(string $token): bool
    {
        $telegramBot = $this->factoryBotApi->make($token);

        return (bool) $telegramBot->setWebhook(
            url: $this->urlWebhook,
            allowUpdates: [
                'message',
                'edited_message',
                'message_reaction',
            ],
            secretToken: static::generateSecretToken($token)
        );
    }

    /**
     * Генерация секретного токена для заголовков хука от тг бота
     *
     * @param string $botToken токен ТГ бота
     * @return string секретный токен для заголовка ``X-Telegram-Bot-Api-Secret-Token``
     */
    public static function generateSecretToken(string $botToken): string
    {
        return Uuid::uuid5(ns: Uuid::NAMESPACE_DNS, name: $botToken)->toString();
    }

    /**
     * Валидация хука сообщения
     *
     * @param string $secretHeader получение секретного токена из заголовков запроса хука
     * @return bool                ответ при валидации хука
     */
    public function isValidWebhook(string $secretHeader): bool
    {
        /** @var TelegramConnection $secret */
        $secret = $this->telegramRepo->getBySecret($secretHeader);

        return hash_equals($secret->webhook_secret, $secretHeader);
    }

    /**
     * Валидация хука из виджета. Для сохранения токена тг бота
     *
     * @param ServerRequestInterface $request получение секретного ключа из заголовков запроса хука из виджета
     * @return bool                           ответ при валидации хука
     * @throws DisposableTokenExpiredException
     * @throws DisposableTokenInvalidDestinationException
     */
    public function isValidSettings(ServerRequestInterface $request): bool
    {
        $jwtToken = $request->getParsedBody()["jwt_token"];

        try {
            $apiClient = $this->amoCRMClientFactory->make();
            $disposableToken = $apiClient->getOAuthClient()->parseDisposableToken($jwtToken);

            return hash_equals((string) $disposableToken->getAccountId(), $request->getParsedBody()['account_id']);
        } catch (DisposableTokenExpiredException $e) {
            // Время жизни токена истекло
            throw new DisposableTokenExpiredException();
        } catch (DisposableTokenInvalidDestinationException $e) {
            // Не прошёл проверку на адресата токена
            throw new DisposableTokenInvalidDestinationException();
        } catch (DisposableTokenVerificationFailedException $e) {
            // Токен не прошел проверку подписи
            return false;
        }
    }
}
