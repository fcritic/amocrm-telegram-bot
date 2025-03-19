<?php

declare(strict_types=1);

namespace Telegram\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель токена для телеграмм бота
 *
 * @property int $id
 * @property int $account_id
 * @property string $token_bot
 * @property string $secret_token
 */
class Telegram extends Model
{
    /**
     * Таблица, связанная с моделью
     *
     * @var string
     */
    protected $table = 'telegram';

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
        'account_id',
        'token_bot',
        'secret_token',
    ];
}
