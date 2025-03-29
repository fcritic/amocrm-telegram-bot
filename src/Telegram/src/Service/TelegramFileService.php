<?php

declare(strict_types=1);

namespace Telegram\Service;

use Chat\Repository\Interface\ExternalUserRepositoryInterface;
use Chat\Repository\Interface\MessageRepositoryInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Telegram\Repository\Interface\TelegramConnectionRepositoryInterface;
use Telegram\Repository\TelegramConnectionRepository;
use Telegram\Service\Factory\TelegramBotApiFactory;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

readonly class FileService
{
    public function __construct(
        protected Client $client,
        protected TelegramBotApiFactory $botFactory,
        protected ExternalUserRepositoryInterface $externalUserRepo,
        protected MessageRepositoryInterface $messageRepo,
        protected TelegramConnectionRepositoryInterface $telegramRepo,
    ) {
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getFile(string $fileId, string $schema): ResponseInterface
    {
        match ($schema) {
            'avatar' => $token = $this->getTokenByAvatar($fileId),
            'file'   => $token = $this->getTokenByMedia($fileId),
            default  => throw new RuntimeException("File id or schema required"),
        };

        if (! $token) {
            throw new NotFoundResourceException('File not found', 400);
        }

        $bot = $this->botFactory->make($token);
        $fileUrl = $bot->makeFileUrl($bot->getFile($fileId));

        return $this->client->get($fileUrl, ['stream' => true]);
    }

    /**
     * @throws Exception
     */
    public function getFileId(int $telegramUserId, string $webhookSecret): string
    {
        $token = $this->telegramRepo->getBySecret($webhookSecret)->token_bot;
        $bot = $this->botFactory->make($token);

        return $bot->getUserProfilePhotos($telegramUserId)->photos[0][2]->fileId;
    }

    protected function getTokenByAvatar(string $fileId): ?string
    {
        return $this->externalUserRepo->getTokenByAvatar($fileId);
    }

    protected function getTokenByMedia(string $fileId): ?string
    {
        return $this->messageRepo->getTokenByMedia($fileId);
    }
}
