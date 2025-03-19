<?php

declare(strict_types=1);

namespace Account\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель аккаунта
 *
 * @property int $id
 * @property string $sub_domain
 * @property int $account_id
 * @property int $account_uid
 * @property int $user_id
 * @property bool $is_active
 */
class Account extends Model
{
    /**
     * Таблица, связанная с моделью
     *
     * @var string
     */
    protected $table = 'account';

    /** @var string */
    protected $primaryKey = 'id';

    /**
     * Указывает, автоматически ли увеличивается идентификатор модели
     *
     * @var bool
     */
    public $timestamps = true;

    /** @var string[] поля в базе */
    protected $fillable = [
        'id',
        'sub_domain',
        'account_id',
        'account_uid',
        'user_id',
        'is_active',
    ];
}
