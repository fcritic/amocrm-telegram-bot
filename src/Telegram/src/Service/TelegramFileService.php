<?php

declare(strict_types=1);

namespace Telegram\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Integration\Repository\Interface\ExternalUserRepositoryInterface;
use Integration\Repository\Interface\MessageRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Telegram\Factory\TelegramBotApiFactory;
use Telegram\Repository\Interface\TelegramConnectionRepositoryInterface;
use Vjik\TelegramBot\Api\FailResult;
use Vjik\TelegramBot\Api\Type\UserProfilePhotos;

readonly class TelegramFileService
{
    public function __construct(
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
        $file = $bot->getFile($fileId);

        if ($file instanceof FailResult) {
            throw new RuntimeException($file->description);
        }

        $fileUrl = $bot->makeFileUrl($file);

        return (new Client())->get($fileUrl, [
            'stream' => true,
            'timeout' => 6,
        ]);
    }

    /**
     * @throws Exception
     */
    public function getAvatarFileId(int $telegramUserId, string $webhookSecret): ?string
    {
        $token = $this->telegramRepo->getSecret($webhookSecret)->token_bot;
        $bot = $this->botFactory->make($token);
        $userProfilePhotos = $bot->getUserProfilePhotos($telegramUserId);

        if ($userProfilePhotos instanceof UserProfilePhotos) {
            if ($userProfilePhotos->totalCount <= 0) {
                return null;
            }
            return $userProfilePhotos?->photos[0][2]?->fileId;
        }
        return null;
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
