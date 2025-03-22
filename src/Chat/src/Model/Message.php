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
 * @property string $amo_message_id
 * @property int $telegram_message_id
 * @property string $type
 * @property string $content
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
        'amo_message_id',
        'telegram_message_id',
        'type',
        'content',
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
        'telegram_message_id' => 'integer',
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
}
