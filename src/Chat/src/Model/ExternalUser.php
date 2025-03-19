<?php

declare(strict_types=1);

namespace Chat\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель Контакта
 *
 * @property int $id
 * @property string $account_id
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
     * Таблица, связанная с моделью
     *
     * @var string
     */
    protected $table = 'external_user';

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
        'amocrm_uid',
        'telegram_id',
        'username',
        'name',
        'number',
        'avatar',
        'profile_link',
    ];
}
