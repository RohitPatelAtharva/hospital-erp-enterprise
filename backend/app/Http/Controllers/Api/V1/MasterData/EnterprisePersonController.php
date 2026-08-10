<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Requests\Api\V1\MasterData\CreateEnterprisePersonRequest;
use App\Http\Responses\ApiResponse;
use App\Services\MasterData\BaseMasterDataService;
use App\Services\MasterData\EnterprisePersonService;
use Illuminate\Http\JsonResponse;

/** Enterprise person index (EPI) controller (10-API §12). */
final class EnterprisePersonController extends BaseMasterDataController
{
    public function __construct(private readonly EnterprisePersonService $service)
    {
    }

    protected function service(): BaseMasterDataService
    {
        return $this->service;
    }

    public function store(CreateEnterprisePersonRequest $request): JsonResponse
    {
        $person = $this->service->create($request->validated());

        return ApiResponse::created($person);
    }
}
