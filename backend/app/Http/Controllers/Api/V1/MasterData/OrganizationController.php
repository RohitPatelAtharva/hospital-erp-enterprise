<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Api\V1\MasterData\Concerns\HandlesChildResources;
use App\Http\Controllers\Api\V1\MasterData\Concerns\LifecycleControllerActions;
use App\Http\Requests\Api\V1\MasterData\CreateOrganizationContactRequest;
use App\Http\Requests\Api\V1\MasterData\CreateOrganizationIdentifierRequest;
use App\Http\Requests\Api\V1\MasterData\CreateOrganizationRelationshipRequest;
use App\Http\Requests\Api\V1\MasterData\CreateOrganizationRequest;
use App\Http\Requests\Api\V1\MasterData\RotateIdentifierRequest;
use App\Http\Requests\Api\V1\MasterData\UpdateOrganizationRequest;
use App\Http\Responses\ApiResponse;
use App\Models\MasterData\OrganizationContact;
use App\Models\MasterData\OrganizationIdentifier;
use App\Models\MasterData\OrganizationRelationship;
use App\Services\MasterData\BaseMasterDataService;
use App\Services\MasterData\OrganizationService;
use Illuminate\Http\JsonResponse;

/** Organization aggregate controller (10-API §10). */
final class OrganizationController extends BaseMasterDataController
{
    use HandlesChildResources;
    use LifecycleControllerActions;

    public function __construct(private readonly OrganizationService $service)
    {
    }

    protected function service(): BaseMasterDataService
    {
        return $this->service;
    }

    public function store(CreateOrganizationRequest $request): JsonResponse
    {
        $organization = $this->service->create($request->validated());

        return ApiResponse::created($organization);
    }

    public function update(UpdateOrganizationRequest $request, string $id): JsonResponse
    {
        return ApiResponse::data($this->service->update($this->service->find($id), $request->validated()));
    }

    // --- Identifiers -------------------------------------------------------
    public function identifiers(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), OrganizationIdentifier::class, 'organization_id');
    }

    public function storeIdentifier(CreateOrganizationIdentifierRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), OrganizationIdentifier::class, 'organization_id', $request->validated());
    }

    public function rotateIdentifier(RotateIdentifierRequest $request, string $id, string $identifierId): JsonResponse
    {
        return $this->rotateIdentifierAction(
            $this->service->find($id),
            $identifierId,
            OrganizationIdentifier::class,
            'organization_id',
            $request->validated('value'),
        );
    }

    // --- Contacts ----------------------------------------------------------
    public function contacts(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), OrganizationContact::class, 'organization_id');
    }

    public function storeContact(CreateOrganizationContactRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), OrganizationContact::class, 'organization_id', $request->validated());
    }

    // --- Relationships -----------------------------------------------------
    public function relationships(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), OrganizationRelationship::class, 'organization_id');
    }

    public function storeRelationship(CreateOrganizationRelationshipRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), OrganizationRelationship::class, 'organization_id', $request->validated());
    }
}
