<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

class CreateConversationTable extends Migration
{
    /**
     * Выполните миграцию
     */
    public function up(): void
    {
        Capsule::schema()->create('conversation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('external_user_id')->unsigned();
            $table->string('telegram_chat_id');
            $table->string('amocrm_chat_id');
            $table->timestamps();

            $table
                ->foreign('external_user_id')
                ->references('id')
                ->on('external_user')
                ->onDelete('cascade');
        });
    }

    /**
     * Откат миграции
     */
    public function down(): void
    {
        Capsule::schema()->table('conversation', function (Blueprint $table) {
            $table->dropForeign(['external_user_id']);
        });
        Capsule::schema()->drop('conversation');
    }
}
