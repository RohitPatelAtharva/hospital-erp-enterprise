<?php

declare(strict_types=1);

namespace App\Repositories\MasterData;

use App\Models\MasterData\EnterprisePerson;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/** Aggregate repository for {@see EnterprisePerson}. */
final class EnterprisePersonRepository extends BaseRepository
{
    protected function model(): Model
    {
        return new EnterprisePerson();
    }
}
