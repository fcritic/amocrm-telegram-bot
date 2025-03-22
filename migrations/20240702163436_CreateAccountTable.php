<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

/**
 * Миграция таблицы Пользователей
 */
class CreateAccountTable extends Migration
{
    /**
     * Выполните миграцию
     */
    public function up(): void
    {
        Capsule::schema()->create('account', function (Blueprint $table) {
            $table->increments('id')->comment('Локальный идентификатор');
            $table->string('sub_domain', 63)->index()->comment('Субдомен amoCRM');
            $table->unsignedInteger('amo_account_id')->unique()->comment('ID аккаунта в amoCRM');
            $table->uuid('amojo_id')->unique()->comment('UUID из API чатов amoCRM');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['sub_domain', 'amo_account_id'], 'account_identity');
        });
    }

    /**
     * Откат миграции
     */
    public function down(): void
    {
        Capsule::schema()->dropIfExists('account');
    }
}
