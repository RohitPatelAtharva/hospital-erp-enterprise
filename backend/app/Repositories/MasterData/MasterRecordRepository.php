<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\MasterRecord;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see MasterRecord}. */
final class MasterRecordRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new MasterRecord();
    }
}
