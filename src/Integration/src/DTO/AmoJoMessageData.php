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
            'type' => 'account_uid',
            'value' => $this->event->getAccountUid()
        ];
    }

    /**
     * @return string|null
     */
    public function getConversationId(): ?string
    {
        return $this->event->getConversation()->getId();
    }

    /**
     * @return string|null
     */
    public function getConversationRefId(): ?string
    {
        return $this->event->getConversation()->getRefId();
    }

    /**
     * @return string|null
     */
    public function getReceiverId(): ?string
    {
        return $this->event->getReceiver()->getId();
    }

    /**
     * @return string|null
     */
    public function getReceiverRefId(): ?string
    {
        return $this->event->getReceiver()->getRefId();
    }

    /**
     * @return string|null
     */
    public function getSenderId(): ?string
    {
        return $this->event->getSender()->getId();
    }

    /**
     * @return string|null
     */
    public function getSenderRefId(): ?string
    {
        return $this->event->getSender()->getRefId();
    }

    /**
     * @return string|null
     */
    public function getReceiverName(): ?string
    {
        return $this->event->getReceiver()->getName();
    }

    /**
     * @return string|null
     */
    public function getSenderName(): ?string
    {
        return $this->event->getSender()->getName();
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->event->getReceiver()->getProfile()?->getPhone();
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->event->getReceiver()->getAvatar();
    }

    /**
     * @return string|null
     */
    public function getProfileLink(): ?string
    {
        return $this->event->getReceiver()->getProfileLink();
    }

    /**
     * @return string|null
     */
    public function getMessageId(): ?string
    {
        return $this->event->getMessage()->getUid();
    }

    /**
     * @return string|null
     */
    public function getMessageRefId(): ?string
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
    public function getMessageText(): ?string
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
