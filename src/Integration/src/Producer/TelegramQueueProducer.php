<?php

declare(strict_types=1);

namespace Integration\Producer;

final class TelegramQueueProducer extends AbstractQueueProducer
{
    /**
     * Просматриваемая очередь
     * @return string
     */
    protected function getQueueName(): string
    {
        return 'telegram_queue';
    }
}
