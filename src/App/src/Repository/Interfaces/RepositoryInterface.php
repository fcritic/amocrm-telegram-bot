<?php

declare(strict_types=1);

namespace App\Repository\Interfaces;

use Illuminate\Database\Eloquent\Model;

/**
 * Интерфейс базового репозитория
 */
interface RepositoryInterface
{
    public function getBy(string $field, mixed $value): ?Model;

    public function create(array $attributes): Model;

    public function firstOrCreate(array $attributes, array $values): Model;

    public function updateOrCreate(array $attributes, array $values): Model;

    public function update(array $attributes, int $id): bool;
}
