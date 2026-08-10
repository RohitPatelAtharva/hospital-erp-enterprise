<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Requests\Api\V1\MasterData\CreateReferenceValueRequest;
use App\Http\Responses\ApiResponse;
use App\Services\MasterData\BaseMasterDataService;
use App\Services\MasterData\ReferenceValueService;
use Illuminate\Http\JsonResponse;

/** Reference value controller (10-API §11). */
final class ReferenceValueController extends BaseMasterDataController
{
    public function __construct(private readonly ReferenceValueService $service)
    {
    }

    protected function service(): BaseMasterDataService
    {
        return $this->service;
    }

    public function store(CreateReferenceValueRequest $request): JsonResponse
    {
        $value = $this->service->create($request->validated());

        return ApiResponse::created($value);
    }
}
