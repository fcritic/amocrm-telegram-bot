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
    /** @var Model Модель репозитория */
    protected Model $model;

    public function __construct()
    {
        $modelClass = $this->getModelClass();
        $this->model = new ($modelClass);
    }

    /**
     * Создает новый экземпляр запроса
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Возвращает FQCN класса модели для создания запроса из репозитория
     *
     * @return string
     */
    abstract public function getModelClass(): string;

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
