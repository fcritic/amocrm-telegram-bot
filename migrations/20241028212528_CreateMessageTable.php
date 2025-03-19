<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

class CreateMessageTable extends Migration
{
    /**
     * Выполните миграцию
     */
    public function up(): void
    {
        Capsule::schema()->create('message', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('conversation_id')->unsigned();
            $table->string('amocrm_msg_id')->nullable();
            $table->string('telegram_msg_id');
            $table->integer('sender_id')->unsigned();
            $table->integer('receiver_id')->unsigned()->nullable();
            $table->string('type');
            $table->text('text')->nullable();
            $table->string('media')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();

            $table
                ->foreign('conversation_id')
                ->references('id')
                ->on('conversation')
                ->onDelete('cascade');
            $table
                ->foreign('sender_id')
                ->references('id')
                ->on('external_user')
                ->onDelete('cascade');
            $table
                ->foreign('receiver_id')
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
        Capsule::schema()->table('message', function (Blueprint $table) {
            $table->dropForeign(['conversation_id']);
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
        });
        Capsule::schema()->dropIfExists('message');
    }
}
