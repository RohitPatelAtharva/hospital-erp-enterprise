<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Minimal read/write seam for the repository layer.
 *
 * Repositories are thin wrappers over the model that carry the approved
 * tenant scoping and keep query logic reusable; business rules live in
 * services/actions, not repositories.
 */
interface RepositoryInterface
{
    public function find(string|int $id): ?Model;

    public function findOrFail(string|int $id): Model;

    public function create(array $attributes): Model;

    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;
}
