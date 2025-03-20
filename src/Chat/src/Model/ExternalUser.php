<?php

declare(strict_types=1);

namespace Chat\Model;

use Account\Model\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель Контакта
 *
 * @property int $id
 * @property int $account_id
 * @property string $amocrm_uid
 * @property string $telegram_id
 * @property string $username
 * @property string $name
 * @property string $number
 * @property string $avatar
 * @property string $profile_link
 */
class ExternalUser extends Model
{
    /**
     * Таблица связанная с моделью
     * @var string
     */
    protected $table = 'external_user';

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
        'amocrm_uid',
        'telegram_id',
        'username',
        'name',
        'number',
        'avatar',
        'profile_link',
    ];

    /**
     * Типы атрибутов
     * @var array<string, string>
     */
    protected $casts = [
        'account_id' => 'integer'
    ];

    /**
     * Таблица принадлежит к таблице
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Связь один ко многим
     * @return HasMany
     */
    public function conversation(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
