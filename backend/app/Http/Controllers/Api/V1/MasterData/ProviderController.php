<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Api\V1\MasterData\Concerns\HandlesChildResources;
use App\Http\Controllers\Api\V1\MasterData\Concerns\LifecycleControllerActions;
use App\Http\Requests\Api\V1\MasterData\CreateProviderCredentialRequest;
use App\Http\Requests\Api\V1\MasterData\CreateProviderIdentifierRequest;
use App\Http\Requests\Api\V1\MasterData\CreateProviderNetworkRequest;
use App\Http\Requests\Api\V1\MasterData\CreateProviderRequest;
use App\Http\Requests\Api\V1\MasterData\RotateIdentifierRequest;
use App\Http\Requests\Api\V1\MasterData\UpdateProviderRequest;
use App\Http\Responses\ApiResponse;
use App\Models\MasterData\ProviderCredential;
use App\Models\MasterData\ProviderIdentifier;
use App\Models\MasterData\ProviderNetwork;
use App\Services\MasterData\BaseMasterDataService;
use App\Services\MasterData\ProviderService;
use Illuminate\Http\JsonResponse;

/** Provider aggregate controller (10-API §9). */
final class ProviderController extends BaseMasterDataController
{
    use HandlesChildResources;
    use LifecycleControllerActions;

    public function __construct(private readonly ProviderService $service)
    {
    }

    protected function service(): BaseMasterDataService
    {
        return $this->service;
    }

    public function store(CreateProviderRequest $request): JsonResponse
    {
        $provider = $this->service->create($request->validated());

        return ApiResponse::created($provider);
    }

    public function update(UpdateProviderRequest $request, string $id): JsonResponse
    {
        return ApiResponse::data($this->service->update($this->service->find($id), $request->validated()));
    }

    // --- Identifiers -------------------------------------------------------
    public function identifiers(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), ProviderIdentifier::class, 'provider_id');
    }

    public function storeIdentifier(CreateProviderIdentifierRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), ProviderIdentifier::class, 'provider_id', $request->validated());
    }

    public function rotateIdentifier(RotateIdentifierRequest $request, string $id, string $identifierId): JsonResponse
    {
        return $this->rotateIdentifierAction(
            $this->service->find($id),
            $identifierId,
            ProviderIdentifier::class,
            'provider_id',
            $request->validated('value'),
        );
    }

    // --- Credentials -------------------------------------------------------
    public function credentials(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), ProviderCredential::class, 'provider_id');
    }

    public function storeCredential(CreateProviderCredentialRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), ProviderCredential::class, 'provider_id', $request->validated());
    }

    // --- Networks ----------------------------------------------------------
    public function networks(string $id): JsonResponse
    {
        return $this->childIndex($this->service->find($id), ProviderNetwork::class, 'provider_id');
    }

    public function storeNetwork(CreateProviderNetworkRequest $request, string $id): JsonResponse
    {
        return $this->childStore($this->service->find($id), ProviderNetwork::class, 'provider_id', $request->validated());
    }
}
