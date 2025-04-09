<?php

declare(strict_types=1);

namespace Integration\DTO;

use Integration\Enum\EventType;

/**
 * Интерфейс для передачи DTO в сервис базы
 */
interface MessageDataInterface
{
    /**
     * @return self
     */
    public static function create(array $params): self;

    /**
     * @return array
     */
    public function getAccountIdentifier(): array;

    /**
     * @return EventType
     */
    public function getEvent(): EventType;

    /**
     * @return string|null
     */
    public function getExternalChatId(): ?string;

    /**
     * @return string|null
     */
    public function getAmoChatId(): ?string;

    /**
     * @return string|null
     */
    public function getAmoUserId(): ?string;

    /**
     * @return string|null
     */
    public function getExternalUserId(): ?string;

    /**
     * @return string|null
     */
    public function getExternalUserName(): ?string;

    /**
     * @return string|null
     */
    public function getExternalUserPhone(): ?string;

    /**
     * @return string|null
     */
    public function getExternalUserUsername(): ?string;

    /**
     * @return string|null
     */
    public function getExternalUserAvatar(): ?string;

    /**
     * @return string|null
     */
    public function getExternalUserProfileLink(): ?string;

    /**
     * @return string|null
     */
    public function getAmoMessageId(): ?string;

    /**
     * @return string|null
     */
    public function getExternalMessageId(): ?string;

    /**
     * @return string|null
     */
    public function getMessageType(): ?string;

    /**
     * @return string|null
     */
    public function getMessageContent(): ?string;

    /**
     * @return string|null
     */
    public function getMedia(): ?string;

    /**
     * @return string|null
     */
    public function getFileName(): ?string;

    /**
     * @return string|null
     */
    public function getFileSize(): ?int;
}
