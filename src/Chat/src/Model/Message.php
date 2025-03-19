<?php

declare(strict_types=1);

namespace Chat\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель сообщения
 *
 * @property int $id
 * @property int $conversation_id
 * @property string $amocrm_msg_id
 * @property string $telegram_msg_id
 * @property int $sender_id
 * @property int $receiver_id
 * @property string $type
 * @property string $text
 * @property int $media
 * @property string $file_name
 * @property int $file_size
 */
class Message extends Model
{
    /**
     * Таблица, связанная с моделью
     *
     * @var string
     */
    protected $table = 'message';

    /** @var string */
    protected $primaryKey = 'id';

    /**
     * Указывает, должна ли модель иметь временную метку
     *
     * @var bool
     */
    public $timestamps = true;

    /** @var string[] */
    protected $fillable = [
        'id',
        'conversation_id',
        'amocrm_msg_id',
        'telegram_msg_id',
        'sender_id',
        'receiver_id',
        'type',
        'text',
        'media',
        'file_name',
        'file_size',
    ];
}
