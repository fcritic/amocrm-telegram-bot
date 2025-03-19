<?php

declare(strict_types=1);

namespace App\Repository;

use App\Repository\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Базовый абстрактный класс репозитория
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @return string
     */
    abstract public function getModelClass(): string;

    /**
     * @return Builder
     */
    protected function query(): Builder
    {
        /** @var Model $modelClass */
        $modelClass = $this->getModelClass();
        return $modelClass::query();
    }

    /**
     * Получения модели из БД, необходимо передавать
     * поле по которому происходит поиск и значение которое ищется
     *
     * @param string $field
     * @param mixed $value
     * @return Model|null
     */
    public function getBy(string $field, mixed $value): ?Model
    {
        return $this->query()->where($field, $value)?->first();
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function firstOrCreate(array $attributes, array $values): Model
    {
        return $this->query()->firstOrCreate($attributes, $values);
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values): Model
    {
        return $this->query()->updateOrCreate($attributes, $values);
    }

    /**
     * @param array $attributes
     * @param int $id
     * @return bool
     */
    public function update(array $attributes, int $id): bool
    {
        return $this->query()->findOrFail($id)?->update($attributes);
    }
}
