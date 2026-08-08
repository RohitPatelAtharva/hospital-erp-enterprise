<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic repository over a single model.
 *
 * Subclasses provide the concrete model via model(); queries inherit the
 * model's tenant scoping (BaseModel) so no cross-tenant reads occur by default.
 */
abstract class BaseRepository implements RepositoryInterface
{
    abstract protected function model(): Model;

    public function newQuery(): Builder
    {
        return $this->model()->newQuery();
    }

    public function find(string|int $id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    public function findOrFail(string|int $id): Model
    {
        return $this->newQuery()->findOrFail($id);
    }

    public function create(array $attributes): Model
    {
        return $this->model()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->update($attributes);

        return $model->fresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
