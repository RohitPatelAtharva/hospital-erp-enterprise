<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData\Concerns;

use App\Http\Responses\ApiResponse;
use App\Services\MasterData\BaseMasterDataService;
use Illuminate\Http\JsonResponse;

/**
 * Lifecycle actions (02-Workflow.md §4) shared by the entity controllers.
 *
 * Each action resolves the tenant-scoped aggregate through the controller's
 * service and delegates the state transition + audit to the service layer.
 * Purge is the elevated governed delete and returns 204. Concrete controllers
 * implement `update(Update*Request, string $id)` with their own FormRequest.
 */
trait LifecycleControllerActions
{
    abstract protected function service(): BaseMasterDataService;

    public function deactivate(string $id): JsonResponse
    {
        return ApiResponse::data($this->service()->deactivate($this->service()->find($id)));
    }

    public function reactivate(string $id): JsonResponse
    {
        return ApiResponse::data($this->service()->reactivate($this->service()->find($id)));
    }

    public function archive(string $id): JsonResponse
    {
        return ApiResponse::data($this->service()->archive($this->service()->find($id)));
    }

    public function restore(string $id): JsonResponse
    {
        return ApiResponse::data($this->service()->restore($this->service()->find($id)));
    }

    public function purge(string $id): JsonResponse
    {
        $this->service()->purge($this->service()->find($id));

        return ApiResponse::noContent();
    }
}
