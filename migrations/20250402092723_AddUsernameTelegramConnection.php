<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

class AddUsernameTelegramConnection extends Migration
{
    /**
     * Выполните миграцию
     */
    public function up(): void
    {
        Capsule::schema()->table('telegram_connection', function (Blueprint $table) {
            $table->string('username_bot', 50)->comment('@username телеграм бот. Используется для источника');
        });
    }

    /**
     * Откат миграции
     */
    public function down(): void
    {
        Capsule::schema()->table('telegram_connection', function (Blueprint $table) {
            $table->dropColumn('username_bot');
        });
    }
}
