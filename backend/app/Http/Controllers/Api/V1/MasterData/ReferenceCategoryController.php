<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Requests\Api\V1\MasterData\CreateReferenceCategoryRequest;
use App\Http\Responses\ApiResponse;
use App\Services\MasterData\BaseMasterDataService;
use App\Services\MasterData\ReferenceCategoryService;
use Illuminate\Http\JsonResponse;

/** Reference category controller (10-API §11). */
final class ReferenceCategoryController extends BaseMasterDataController
{
    public function __construct(private readonly ReferenceCategoryService $service)
    {
    }

    protected function service(): BaseMasterDataService
    {
        return $this->service;
    }

    public function store(CreateReferenceCategoryRequest $request): JsonResponse
    {
        $category = $this->service->create($request->validated());

        return ApiResponse::created($category);
    }
}
