<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\Patient;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see Patient}. */
final class PatientRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new Patient();
    }
}
