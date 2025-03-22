<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

/**
 * Миграция таблицы Токенов TelegramConnection
 */
class CreateTelegramConnectionTable extends Migration
{
    /**
     * Выполните миграцию
     *
     * @return void void
     */
    public function up(): void
    {
        Capsule::schema()->create('telegram_connection', function (Blueprint $table) {
            $table->increments('id')->comment('Локальный идентификатор');
            $table->unsignedInteger('account_id')->unsigned()->comment('Связь с аккаунтом');
            $table->string('token_bot', 128)->unique()->index()->comment('Токен для телеграмм бота');
            $table->string('webhook_secret', 64)->index()->unique()->comment('HMAC для верификации');
            $table->timestamps();

            $table->foreign('account_id')
                ->references('id')
                ->on('account')
                ->onDelete('cascade');
        });
    }

    /**
     * Откат миграции
     *
     * @return void void
     */
    public function down(): void
    {
        Capsule::schema()->table('telegram_connection', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });

        Capsule::schema()->dropIfExists('telegram_connection');
    }
}
