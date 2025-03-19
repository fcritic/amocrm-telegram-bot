<?php

declare(strict_types=1);

namespace Integration\DTO;

interface MessageDataInterface
{
    /**
     * @return array
     */
    public function getAccountIdentifier(): array;

    /**
     * @return string|null
     */
    public function getConversationId(): ?string;

    /**
     * @return string|null
     */
    public function getConversationRefId(): ?string;

    /**
     * @return string|null
     */
    public function getReceiverId(): ?string;

    /**
     * @return string|null
     */
    public function getReceiverRefId(): ?string;

    /**
     * @return string|null
     */
    public function getSenderId(): ?string;

    /**
     * @return string|null
     */
    public function getSenderRefId(): ?string;

    /**
     * @return string|null
     */
    public function getReceiverName(): ?string;

    /**
     * @return string|null
     */
    public function getSenderName(): ?string;

    /**
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * @return string|null
     */
    public function getUsername(): ?string;

    /**
     * @return string|null
     */
    public function getAvatar(): ?string;

    /**
     * @return string|null
     */
    public function getProfileLink(): ?string;

    /**
     * @return string|null
     */
    public function getMessageId(): ?string;

    /**
     * @return string|null
     */
    public function getMessageRefId(): ?string;

    /**
     * @return string|null
     */
    public function getMessageType(): ?string;

    /**
     * @return string|null
     */
    public function getMessageText(): ?string;

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
    public function getFileSize(): ?string;
}
