<?php

declare(strict_types=1);

namespace Integration\Enum;

enum EventType: string
{
    case SEND_MESSAGE = 'sendMessage';
    case EDIT_MESSAGE = 'editMessage';
    case REACTION_MESSAGE = 'reactionMessage';
}
