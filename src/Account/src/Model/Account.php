<?php

declare(strict_types=1);

namespace Account\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Telegram\Model\Telegram;

/**
 * Модель аккаунта
 *
 * @property int $id
 * @property string $sub_domain
 * @property int $account_id
 * @property string $account_uid
 * @property bool $is_active
 */
class Account extends Model
{
    /**
     * Таблица связанная с моделью
     * @var string
     */
    protected $table = 'account';

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
        'sub_domain',
        'account_id',
        'account_uid',
        'is_active',
    ];

    /**
     * Типы атрибутов
     * @var array<string, string>
     */
    protected $casts = [
        'account_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Связь один к одному
     * @return HasOne
     */
    public function telegram(): HasOne
    {
        return $this->hasOne(Telegram::class);
    }

    /**
     * Связь один ко многим
     * @return HasMany
     */
    public function accessToken(): HasMany
    {
        return $this->hasMany(AccessToken::class);
    }
}
