<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\Staff;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see Staff}. */
final class StaffRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new Staff();
    }
}
