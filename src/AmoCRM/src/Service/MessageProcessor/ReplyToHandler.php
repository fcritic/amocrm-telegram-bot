<?php

declare(strict_types=1);

namespace AmoCRM\Service\MessageProcessor;

use AmoJo\Models\Messages\ReplyTo;
use AmoJo\Models\Payload;
use Integration\Repository\Interface\MessageRepositoryInterface;
use Vjik\TelegramBot\Api\Type\Update\Update;

readonly class ReplyToHandler
{
    public function __construct(protected MessageRepositoryInterface $messageRepo)
    {
    }

    /**
     * @param Payload $payload
     * @param Update $event
     * @return void
     */
    public function handle(Payload $payload, Update $event): void
    {
        if (! $event->message?->replyToMessage) {
            return;
        }

        $amoJoRefId = $this->messageRepo->getAmoMessageId(
            $event->message->replyToMessage->messageId
        );

        if ($amoJoRefId !== null) {
            $payload->setReplyTo(
                (new ReplyTo())->setReplyRefUid($amoJoRefId)
            );
        }
    }
}
