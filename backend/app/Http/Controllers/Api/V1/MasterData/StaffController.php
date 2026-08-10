<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Api\V1\MasterData\Concerns\HandlesChildResources;
use App\Http\Controllers\Api\V1\MasterData\Concerns\LifecycleControllerActions;
use App\Http\Requests\Api\V1\MasterData\CreateStaffConsentRequest;
use App\Http\Requests\Api\V1\MasterData\CreateStaffCredentialRequest;
use App\Http\Requests\Api\V1\MasterData\CreateStaffDemographicRequest;
use App\Http\Requests\Api\V1\MasterData\CreateStaffIdentifierRequest;
use App\Http\Requests\Api\V1\MasterData\CreateStaffRequest;
use App\Http\Requests\Api\V1\MasterData\RotateIdentifierRequest;
use App\Http\Requests\Api\V1\MasterData\UpdateStaffRequest;
use App\Http\Responses\ApiResponse;
use App\Models\MasterData\StaffConsent;
use App\Models\MasterData\StaffCredential;
use App\Models\MasterData\StaffDemographic;
use App\Models\MasterData\StaffIdentifier;
use App\Services\MasterData\BaseMasterDataService;
use App\Services\MasterData\StaffService;
use Illuminate\Http\JsonResponse;

/** Staff aggregate controller (10-API §8). */
final class StaffController extends BaseMasterDataController
{
    use HandlesChildResources;
    use LifecycleControllerActions;

    public function __construct(private readonly StaffService $service)
    {
    }

    protected function service(): BaseMasterDataService
    {
        return $this->service;
    }

    public function store(CreateStaffRequest $request): JsonResponse
    {
        $staff = $this->service->create($request->validated());

        return ApiResponse::created($staff);
    }

    public function update(UpdateStaffRequest $request, string $id): JsonResponse
    {
        return ApiResponse::data($this->service->update($this->service->find($id), $request->validated()));
    }

    // --- Identifiers -------------------------------------------------------
    public function identifiers(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), StaffIdentifier::class, 'staff_id');
    }

    public function storeIdentifier(CreateStaffIdentifierRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), StaffIdentifier::class, 'staff_id', $request->validated());
    }

    public function rotateIdentifier(RotateIdentifierRequest $request, string $id, string $identifierId): JsonResponse
    {
        return $this->rotateIdentifierAction(
            $this->service->find($id),
            $identifierId,
            StaffIdentifier::class,
            'staff_id',
            $request->validated('value'),
        );
    }

    // --- Credentials -------------------------------------------------------
    public function credentials(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), StaffCredential::class, 'staff_id');
    }

    public function storeCredential(CreateStaffCredentialRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), StaffCredential::class, 'staff_id', $request->validated());
    }

    // --- Consents ----------------------------------------------------------
    public function consents(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), StaffConsent::class, 'staff_id');
    }

    public function storeConsent(CreateStaffConsentRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), StaffConsent::class, 'staff_id', $request->validated());
    }

    // --- Demographics ------------------------------------------------------
    public function demographics(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), StaffDemographic::class, 'staff_id');
    }

    public function storeDemographic(CreateStaffDemographicRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), StaffDemographic::class, 'staff_id', $request->validated());
    }
}
