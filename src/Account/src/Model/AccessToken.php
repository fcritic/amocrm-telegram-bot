<?php

declare(strict_types=1);

namespace Account\Model;

use Illuminate\Database\Eloquent\Model;

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
     * Таблица, связанная с моделью
     *
     * @var string
     */
    protected $table = 'access_token';

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
        'access_token',
        'refresh_token',
        'expires',
    ];
}
