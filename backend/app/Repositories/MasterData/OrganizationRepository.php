<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\Organization;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see Organization}. */
final class OrganizationRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new Organization();
    }
}
