<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Responses\ApiResponse;
use App\Services\MasterData\VersionService;
use Illuminate\Http\JsonResponse;

/**
 * Version history controller (10-API §18).
 *
 * Version history is read-only (masterdata:read); version rows are created by
 * the service layer on every update, never directly by the API.
 */
final class VersionController
{
    public function __construct(private readonly VersionService $service)
    {
    }

    public function index(string $masterRecordId): JsonResponse
    {
        $versions = $this->service->forMasterRecord($masterRecordId);

        return ApiResponse::success($versions);
    }
}
