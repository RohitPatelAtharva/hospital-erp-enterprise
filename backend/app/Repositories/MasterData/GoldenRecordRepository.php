<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\GoldenRecord;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see GoldenRecord}. */
final class GoldenRecordRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new GoldenRecord();
    }
}
