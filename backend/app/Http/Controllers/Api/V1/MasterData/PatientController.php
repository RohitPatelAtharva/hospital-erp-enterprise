<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Api\V1\MasterData\Concerns\HandlesChildResources;
use App\Http\Controllers\Api\V1\MasterData\Concerns\LifecycleControllerActions;
use App\Http\Requests\Api\V1\MasterData\CreatePatientAliasRequest;
use App\Http\Requests\Api\V1\MasterData\CreatePatientConsentRequest;
use App\Http\Requests\Api\V1\MasterData\CreatePatientDemographicRequest;
use App\Http\Requests\Api\V1\MasterData\CreatePatientIdentifierRequest;
use App\Http\Requests\Api\V1\MasterData\CreatePatientRelationRequest;
use App\Http\Requests\Api\V1\MasterData\CreatePatientRequest;
use App\Http\Requests\Api\V1\MasterData\RotateIdentifierRequest;
use App\Http\Requests\Api\V1\MasterData\UpdatePatientRequest;
use App\Http\Responses\ApiResponse;
use App\Models\MasterData\PatientAlias;
use App\Models\MasterData\PatientConsent;
use App\Models\MasterData\PatientDemographic;
use App\Models\MasterData\PatientIdentifier;
use App\Models\MasterData\PatientRelation;
use App\Services\MasterData\BaseMasterDataService;
use App\Services\MasterData\PatientService;
use Illuminate\Http\JsonResponse;

/** Patient aggregate controller (10-API §7). */
final class PatientController extends BaseMasterDataController
{
    use HandlesChildResources;
    use LifecycleControllerActions;

    public function __construct(private readonly PatientService $service)
    {
    }

    protected function service(): BaseMasterDataService
    {
        return $this->service;
    }

    public function store(CreatePatientRequest $request): JsonResponse
    {
        $patient = $this->service->create($request->validated());

        return ApiResponse::created($patient);
    }

    public function update(UpdatePatientRequest $request, string $id): JsonResponse
    {
        return ApiResponse::data($this->service->update($this->service->find($id), $request->validated()));
    }

    // --- Identifiers -------------------------------------------------------
    public function identifiers(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), PatientIdentifier::class, 'patient_id');
    }

    public function storeIdentifier(CreatePatientIdentifierRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), PatientIdentifier::class, 'patient_id', $request->validated());
    }

    public function rotateIdentifier(RotateIdentifierRequest $request, string $id, string $identifierId): JsonResponse
    {
        return $this->rotateIdentifierAction(
            $this->service->find($id),
            $identifierId,
            PatientIdentifier::class,
            'patient_id',
            $request->validated('value'),
        );
    }

    // --- Demographics ------------------------------------------------------
    public function demographics(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), PatientDemographic::class, 'patient_id');
    }

    public function storeDemographic(CreatePatientDemographicRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), PatientDemographic::class, 'patient_id', $request->validated());
    }

    // --- Consents ----------------------------------------------------------
    public function consents(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), PatientConsent::class, 'patient_id');
    }

    public function storeConsent(CreatePatientConsentRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), PatientConsent::class, 'patient_id', $request->validated());
    }

    // --- Relations ---------------------------------------------------------
    public function relations(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), PatientRelation::class, 'patient_id');
    }

    public function storeRelation(CreatePatientRelationRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), PatientRelation::class, 'patient_id', $request->validated());
    }

    // --- Aliases -----------------------------------------------------------
    public function aliases(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), PatientAlias::class, 'patient_id');
    }

    public function storeAlias(CreatePatientAliasRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), PatientAlias::class, 'patient_id', $request->validated());
    }
}
