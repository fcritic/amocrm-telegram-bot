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
            $table->increments('id');
            $table->string('sub_domain')->index();
            $table->integer('account_id')->index();
            $table->string('account_uid');
            $table->boolean('is_active');
            $table->timestamps();

            $table->unique(['sub_domain', 'account_id'], 'subdomain_account_unique');
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
