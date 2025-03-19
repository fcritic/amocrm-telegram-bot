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
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->string('amocrm_uid', 255)->index();
            $table->string('telegram_id', 255)->index()->nullable();
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->string('number')->nullable();
            $table->text('avatar')->nullable();
            $table->text('profile_link')->nullable();
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
        Capsule::schema()->table('external_user', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
        Capsule::schema()->dropIfExists('external_user');
    }
}
