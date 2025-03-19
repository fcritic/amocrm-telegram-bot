<?php

declare(strict_types=1);

namespace Telegram\Service;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Telegram\Repository\Interface\TelegramRepositoryInterface;
use Telegram\Service\Factory\TelegramBotApiFactory;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

readonly class FileService
{
    public function __construct(
        protected Client $client,
        protected TelegramBotApiFactory $botFactory,
        protected TelegramRepositoryInterface $telegramRepo,
    ) {
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getFile(string $fileId, string $schema): ResponseInterface
    {
        match ($schema) {
            'avatar' => $token = $this->telegramRepo->getAvatarBotToken($fileId),
            'file'   => $token = $this->telegramRepo->getMediaBotToken($fileId),
            default  => throw new RuntimeException("File id or schema required"),
        };

        if (! $token) {
            throw new NotFoundResourceException('File not found', 400);
        }

        $bot = $this->botFactory->make($token);
        $fileUrl = $bot->makeFileUrl($bot->getFile($fileId));

        return $this->client->get($fileUrl, ['stream' => true]);
    }
}
