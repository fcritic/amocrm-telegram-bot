<?php

declare(strict_types=1);

namespace Chat\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property string $media
 * @property string $file_name
 * @property int $file_size
 */
class Message extends Model
{
    /**
     * Таблица связанная с моделью
     * @var string
     */
    protected $table = 'message';

    /**
     * Указывает, что временные метки created_at/updated_at должны использоваться
     * @var bool
     */
    public $timestamps = true;

    /**
     * Поля, разрешенные для массового присваивания
     * @var array<int, string>
     */
    protected $fillable = [
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

    /**
     * Типы атрибутов
     * @var array<string, string>
     */
    protected $casts = [
        'conversation_id' => 'integer',
        'sender_id' => 'integer',
        'receiver_id' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Таблица принадлежит к таблице
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Таблица принадлежит к таблице
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(ExternalUser::class, 'sender_id');
    }

    /**
     * Таблица принадлежит к таблице
     * @return BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(ExternalUser::class, 'receiver_id');
    }
}
