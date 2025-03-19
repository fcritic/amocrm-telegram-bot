<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

/**
 * Миграция таблицы Токенов
 */
class CreateAccessTokenTable extends Migration
{
    /**
     * Выполните миграцию
     */
    public function up(): void
    {
        Capsule::schema()->create('access_token', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->integer('expires');
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
     */
    public function down(): void
    {
        Capsule::schema()->table('access_token', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
        Capsule::schema()->dropIfExists('access_token');
    }
}
