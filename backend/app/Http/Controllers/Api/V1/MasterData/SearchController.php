<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\MasterData\MasterRecord;
use App\Models\MasterData\Organization;
use App\Models\MasterData\Patient;
use App\Models\MasterData\Provider;
use App\Models\MasterData\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tenant-scoped free-text search (10-API §12).
 *
 * Every query is scoped to the active tenant through the model's global scope,
 * so no cross-tenant record can be returned. Results are paginated and returned
 * in the standard envelope. Permission is enforced via route middleware.
 */
final class SearchController extends Controller
{
    public function patients(Request $request): JsonResponse
    {
        return $this->search(Patient::class, $request);
    }

    public function staff(Request $request): JsonResponse
    {
        return $this->search(Staff::class, $request);
    }

    public function providers(Request $request): JsonResponse
    {
        return $this->search(Provider::class, $request);
    }

    public function organizations(Request $request): JsonResponse
    {
        return $this->search(Organization::class, $request);
    }

    public function master(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim();

        return ApiResponse::paginated(
            MasterRecord::query()
                ->when($q->isNotEmpty(), fn ($query) => $query->where('external_ref', 'ilike', '%'.$q.'%'))
                ->orderByDesc('created_at')
                ->paginate(25),
        );
    }

    /**
     * @param  class-string  $modelClass
     */
    private function search(string $modelClass, Request $request): JsonResponse
    {
        $q = $request->string('q')->trim();

        return ApiResponse::paginated(
            $modelClass::query()
                ->when($q->isNotEmpty(), fn ($query) => $query->where('name', 'ilike', '%'.$q.'%'))
                ->orderByDesc('created_at')
                ->paginate(25),
        );
    }
}
