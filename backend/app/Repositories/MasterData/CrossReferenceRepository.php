<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\CrossReference;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see CrossReference}. */
final class CrossReferenceRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new CrossReference();
    }
}
