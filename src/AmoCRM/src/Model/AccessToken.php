<?php

declare(strict_types=1);

namespace AmoCRM\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель токена доступа
 *
 * @property int $id
 * @property int $account_id
 * @property string $access_token
 * @property string $refresh_token
 * @property int $expires
 */
class AccessToken extends Model
{
    /**
     * Таблица связанная с моделью
     * @var string
     */
    protected $table = 'access_token';

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
        'account_id',
        'access_token',
        'refresh_token',
        'expires',
    ];

    /**
     * Типы атрибутов
     * @var array<string, string>
     */
    protected $casts = [
        'account_id' => 'integer',
        'expires' => 'integer',
    ];

    /**
     * Таблица принадлежит к таблице
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
