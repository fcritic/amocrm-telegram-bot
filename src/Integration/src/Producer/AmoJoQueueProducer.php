<?php

declare(strict_types=1);

namespace Integration\Producer;

final class AmoJoQueueProducer extends AbstractQueueProducer
{
    /**
     * Просматриваемая очередь
     * @return string
     */
    protected function getQueueName(): string
    {
        return 'amojo_queue';
    }
}
