<?php

declare(strict_types=1);

namespace Chat\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель Чата
 *
 * @property int $id
 * @property int $external_user_id
 * @property int $telegram_chat_id
 * @property string $amocrm_chat_id
 */
class Conversation extends Model
{
    /**
     * Таблица, связанная с моделью
     *
     * @var string
     */
    protected $table = 'conversation';

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
        'external_user_id',
        'telegram_chat_id',
        'amocrm_chat_id',
    ];
}
