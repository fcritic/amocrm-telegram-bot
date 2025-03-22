<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

class CreateExternalUserTable extends Migration
{
    /**
     * Выполните миграцию
     */
    public function up(): void
    {
        Capsule::schema()->create('external_user', function (Blueprint $table) {
            $table->increments('id')->comment('Локальный идентификатор');
            $table->unsignedInteger('account_id')->unsigned()->comment('Связь с аккаунтом');
            $table->uuid('amo_user_id')->index()->comment('UUID из API чатов amoCRM');
            $table->bigInteger('telegram_user_id')->nullable()->index()->comment('ID в TelegramConnection');
            $table->string('username')->nullable();
            $table->string('name', 191)->nullable()->comment('Имя');
            $table->char('phone', 20)->nullable()->comment('E.164 формат');
            $table->string('avatar', 120)->nullable();
            $table->string('profile_link', 120)->nullable();
            $table->timestamps();

            $table->foreign('account_id')
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
        Capsule::schema()->table('external_user', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
        Capsule::schema()->dropIfExists('external_user');
    }
}
