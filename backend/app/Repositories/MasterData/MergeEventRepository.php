<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\MergeEvent;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see MergeEvent}. */
final class MergeEventRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new MergeEvent();
    }
}
