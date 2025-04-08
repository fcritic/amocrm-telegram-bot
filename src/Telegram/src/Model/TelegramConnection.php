<?php

declare(strict_types=1);

namespace Telegram\Model;

use AmoCRM\Model\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель токена для телеграмм бота
 *
 * @property int $id
 * @property int $account_id
 * @property string $token_bot
 * @property string $webhook_secret
 * @property string $username_bot
 */
class TelegramConnection extends Model
{
    /**
     * Таблица связанная с моделью
     * @var string
     */
    protected $table = 'telegram_connection';

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
        'token_bot',
        'webhook_secret',
        'username_bot',
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
}
