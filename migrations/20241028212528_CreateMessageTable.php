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
            $table->increments('id')->comment('Локальный идентификатор');
            $table->unsignedInteger('conversation_id')->unsigned()->comment('Связь с чатом');
            $table->uuid('amo_message_id')->nullable()->comment('ID сообщения в amoCRM');
            $table->bigInteger('telegram_message_id')->comment('ID сообщения в TelegramConnection');
            $table->string('type');
            $table->text('content')->charset('utf8mb4')->nullable()->comment('Текст сообщения');
            $table->string('media',210)->nullable();
            $table->string('file_name',210)->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('id')
                ->on('conversation')
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
        });
        Capsule::schema()->dropIfExists('message');
    }
}
