<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\ReferenceValue;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see ReferenceValue}. */
final class ReferenceValueRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new ReferenceValue();
    }
}
