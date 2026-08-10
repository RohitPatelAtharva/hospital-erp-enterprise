<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\MasterData\BaseMasterDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shared read surface for the Master Data aggregate resources.
 *
 * Concrete controllers provide their service + a FormRequest for create. The
 * read endpoints (list/find) are permission-agnostic reads that rely on the
 * tenant-scoped query inherited from the repository. Create is delegated to the
 * service, which owns the aggregate transaction + audit.
 */
abstract class BaseMasterDataController extends Controller
{
    abstract protected function service(): BaseMasterDataService;

    protected function indexQuery(): \Illuminate\Contracts\Database\Query\Builder
    {
        return $this->service()->repository()->newQuery();
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->indexQuery();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return ApiResponse::paginated($query->orderByDesc('created_at')->paginate(25));
    }

    public function show(string $id): JsonResponse
    {
        return ApiResponse::data(
            $this->service()->repository()->findOrFail($id),
        );
    }
}
