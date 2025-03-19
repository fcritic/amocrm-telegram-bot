<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

/**
 * Миграция таблицы Токенов Telegram
 */
class CreateTelegramTable extends Migration
{
    /**
     * Выполните миграцию
     *
     * @return void void
     */
    public function up(): void
    {
        Capsule::schema()->create('telegram', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned()->index();
            $table->text('token_bot');
            $table->string('secret_token', 255)->index();
            $table->timestamps();

            $table
                ->foreign('account_id')
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
        Capsule::schema()->table('telegram', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });

        Capsule::schema()->dropIfExists('telegram');
    }
}
