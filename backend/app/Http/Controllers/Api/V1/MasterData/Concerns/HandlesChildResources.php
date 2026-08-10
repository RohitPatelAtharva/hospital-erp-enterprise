<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData\Concerns;

use App\Http\Responses\ApiResponse;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Generic aggregate child-resource handling (10-API.md §7-§10).
 *
 * Child rows (identifiers, demographics, consents, aliases, credentials,
 * networks, contacts, relationships) are listed and created under their parent
 * aggregate. All writes stamp the active tenant explicitly and run inside a
 * transaction; reads are tenant-scoped through the model's global scope.
 */
trait HandlesChildResources
{
    /**
     * List a parent's child records (tenant-scoped, paginated).
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function childIndex(Model $parent, string $modelClass, string $fk): JsonResponse
    {
        return ApiResponse::paginated(
            $modelClass::query()
                ->where($fk, $parent->getKey())
                ->orderByDesc('created_at')
                ->paginate(25),
        );
    }

    /**
     * Create a child record stamped with the parent FK + active tenant.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function childStore(Model $parent, string $modelClass, string $fk, array $data): JsonResponse
    {
        $model = DB::transaction(function () use ($parent, $modelClass, $fk, $data): Model {
            $data[$fk] = $parent->getKey();
            $data['tenant_id'] = (string) TenantContext::tenantId();

            return $modelClass::create($data);
        });

        return ApiResponse::created($model);
    }

    /**
     * Rotate an identifier: deactivate the current row and, when a replacement
     * value is supplied, create a new active identifier of the same type for the
     * same parent (10-API.md §7-§10 `rotate`).
     *
     * @param  class-string<Model>  $modelClass  identifier model class
     */
    protected function rotateIdentifierAction(Model $parent, string $id, string $modelClass, string $parentFk, ?string $newValue): JsonResponse
    {
        $identifier = $modelClass::query()->findOrFail($id);

        DB::transaction(function () use ($identifier, $parent, $modelClass, $parentFk, $newValue): void {
            $identifier->update(['status' => 'inactive']);

            if ($newValue !== null && $newValue !== '') {
                $modelClass::create([
                    $parentFk => $parent->getKey(),
                    'tenant_id' => (string) TenantContext::tenantId(),
                    'identity_type_id' => $identifier->identity_type_id,
                    'value' => $newValue,
                ]);
            }
        });

        return ApiResponse::data($identifier->fresh());
    }
}
