<?php

declare(strict_types=1);

namespace App\Helper;

use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Хелпер - загружает данные в ENV
 */
class ConfigLoader
{
    /**
     * Настройка данных в ENV
     */
    public static function load(string $path = '.env'): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException(".env file not found at: {$path}");
        }

        try {
            (new Dotenv())->load($path);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to load .env: " . $e->getMessage());
        }
    }
}
