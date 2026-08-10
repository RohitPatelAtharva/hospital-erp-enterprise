<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\Version;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see Version}. */
final class VersionRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new Version();
    }
}
