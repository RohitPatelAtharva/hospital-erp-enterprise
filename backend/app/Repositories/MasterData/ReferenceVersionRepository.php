<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\ReferenceVersion;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see ReferenceVersion}. */
final class ReferenceVersionRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new ReferenceVersion();
    }
}
