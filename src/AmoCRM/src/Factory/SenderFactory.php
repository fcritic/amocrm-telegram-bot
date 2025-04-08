<?php

declare(strict_types=1);

namespace AmoCRM\Factory;

use AmoJo\Exception\SenderException;
use AmoJo\Models\Users\Sender;
use Dot\DependencyInjection\Attribute\Inject;
use Exception;
use Vjik\TelegramBot\Api\Type\User;

readonly class SenderFactory
{
    #[Inject('config.host')]
    public function __construct(private string $host)
    {
    }

    /**
     * Создает внешнего юзера для отправки в amoJo по данным из телеграм
     *
     * @param User $user
     * @param string|null $avatarFileId
     * @return Sender
     * @throws Exception
     */
    public function create(User $user, ?string $avatarFileId = null): Sender
    {
        if (! $userId = $user->id) {
            throw new SenderException('User id required');
        }

        $sender = (new Sender())->setId((string) $userId);

        if ($name = $user->firstName) {
            $sender->setName($name);
        }

        if ($avatarFileId !== null) {
            $sender->setAvatar($this->buildAvatarUrl($avatarFileId));
        }

        if ($profileLink = $this->buildProfileLink($user)) {
            $sender->setProfileLink($profileLink);
        }

        return $sender;
    }

    /**
     * @param User $user
     * @return string|null
     */
    private function buildProfileLink(User $user): ?string
    {
        return "https://t.me/{$user->username}" ?? null;
    }

    /**
     * @param string $avatarFileId
     * @return string|null
     */
    private function buildAvatarUrl(string $avatarFileId): ?string
    {
        return "{$this->host}/api/files/{$avatarFileId}?with=avatar";
    }
}
