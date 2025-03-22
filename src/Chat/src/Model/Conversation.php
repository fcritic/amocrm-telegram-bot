<?php

declare(strict_types=1);

namespace Chat\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель Чата
 *
 * @property int $id
 * @property int $external_user_id
 * @property int $telegram_chat_id
 * @property string $amo_chat_id
 */
class Conversation extends Model
{
    /**
     * Таблица связанная с моделью
     * @var string
     */
    protected $table = 'conversation';

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
        'external_user_id',
        'telegram_chat_id',
        'amo_chat_id',
    ];

    /**
     * Типы атрибутов
     * @var array<string, string>
     */
    protected $casts = [
        'external_user_id' => 'integer',
        'telegram_chat_id' => 'integer',
    ];

    /**
     * Таблица принадлежит к таблице
     * @return BelongsTo
     */
    public function externalUser(): BelongsTo
    {
        return $this->belongsTo(ExternalUser::class);
    }

    /**
     * Связь один ко многим
     * @return HasMany
     */
    public function message(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
