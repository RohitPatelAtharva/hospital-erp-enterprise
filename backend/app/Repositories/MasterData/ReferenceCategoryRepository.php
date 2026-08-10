<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\ReferenceCategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see ReferenceCategory}. */
final class ReferenceCategoryRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new ReferenceCategory();
    }
}
