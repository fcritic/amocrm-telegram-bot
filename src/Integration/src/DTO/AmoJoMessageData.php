<?php

namespace Integration\DTO;

use AmoJo\Webhook\OutgoingMessageEvent;

readonly class AmoJoMessageData implements MessageDataInterface
{
    /**
     * @param OutgoingMessageEvent $event
     */
    public function __construct(protected OutgoingMessageEvent $event)
    {
    }

    /**
     * @return array
     */
    public function getAccountIdentifier(): array
    {
        return [
            'type' => 'amojo_id',
            'value' => $this->event->getAccountUid()
        ];
    }

    /**
     * @return string|null
     */
    public function getExternalChatId(): ?string
    {
        return $this->event->getConversation()->getId();
    }

    /**
     * @return string|null
     */
    public function getAmoChatId(): ?string
    {
        return $this->event->getConversation()->getRefId();
    }

    /**
     * @return string|null
     */
    public function getAmoUserId(): ?string
    {
        return $this->event->getReceiver()->getId();
    }

    /**
     * @return string|null
     */
    public function getExternalUserId(): ?string
    {
        return $this->event->getReceiver()->getRefId();
    }

    /**
     * @return string|null
     */
    public function getExternalUserName(): ?string
    {
        return $this->event->getReceiver()->getName();
    }

    /**
     * @return string|null
     */
    public function getExternalUserPhone(): ?string
    {
        return $this->event->getReceiver()->getProfile()?->getPhone();
    }

    /**
     * @return string|null
     */
    public function getExternalUserUsername(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getExternalUserAvatar(): ?string
    {
        return $this->event->getReceiver()->getAvatar();
    }

    /**
     * @return string|null
     */
    public function getExternalUserProfileLink(): ?string
    {
        return $this->event->getReceiver()->getProfileLink();
    }

    /**
     * @return string|null
     */
    public function getExternalMessageId(): ?string
    {
        return $this->event->getMessage()->getUid();
    }

    /**
     * @return string|null
     */
    public function getAmoMessageId(): ?string
    {
        return $this->event->getMessage()->getRefUid();
    }

    /**
     * @return string|null
     */
    public function getMessageType(): ?string
    {
        return $this->event->getMessage()->getType();
    }

    /**
     * @return string|null
     */
    public function getMessageContent(): ?string
    {
        return $this->event->getMessage()->getText();
    }

    /**
     * @return string|null
     */
    public function getMedia(): ?string
    {
        return $this->event->getMessage()->getMedia();
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->event->getMessage()->getFileName();
    }

    /**
     * @return string|null
     */
    public function getFileSize(): ?string
    {
        return $this->event->getMessage()->getFileSize();
    }
}
