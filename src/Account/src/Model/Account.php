<?php

declare(strict_types=1);

namespace Account\Model;

use Chat\Model\ExternalUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Telegram\Model\TelegramConnection;

/**
 * Модель аккаунта
 *
 * @property int $id
 * @property string $sub_domain
 * @property int $amo_account_id
 * @property string $amojo_id
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
        'amo_account_id',
        'amojo_id',
        'is_active',
    ];

    /**
     * Типы атрибутов
     * @var array<string, string>
     */
    protected $casts = [
        'amo_account_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Связь один к одному
     * @return HasOne
     */
    public function telegramConnection(): HasOne
    {
        return $this->hasOne(TelegramConnection::class);
    }

    /**
     * Связь один ко многим
     * @return HasMany
     */
    public function accessToken(): HasMany
    {
        return $this->hasMany(AccessToken::class);
    }

    /**
     * Связь один ко многим
     * @return HasMany
     */
    public function externalUser(): HasMany
    {
        return $this->hasMany(ExternalUser::class);
    }
}
