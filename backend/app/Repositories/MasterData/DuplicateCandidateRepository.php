<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\DuplicateCandidate;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see DuplicateCandidate}. */
final class DuplicateCandidateRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new DuplicateCandidate();
    }
}
