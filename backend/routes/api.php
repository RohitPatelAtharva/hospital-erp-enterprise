<?php

use App\Http\Controllers\Api\V1\MasterData\EnterprisePersonController;
use App\Http\Controllers\Api\V1\MasterData\OrganizationController;
use App\Http\Controllers\Api\V1\MasterData\PatientController;
use App\Http\Controllers\Api\V1\MasterData\ProviderController;
use App\Http\Controllers\Api\V1\MasterData\ReferenceCategoryController;
use App\Http\Controllers\Api\V1\MasterData\ReferenceValueController;
use App\Http\Controllers\Api\V1\MasterData\SearchController;
use App\Http\Controllers\Api\V1\MasterData\StaffController;
use App\Http\Controllers\Api\V1\MasterData\VersionController;
use App\Support\Permissions;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['api.version'])
    ->group(function (): void {
        // --- Public foundation endpoints ---------------------------------
        Route::get('/health', App\Http\Controllers\HealthController::class);
        Route::get('/openapi.json', App\Http\Controllers\Api\Meta\OpenApiController::class);

        // --- Authentication (Laravel Sanctum) ----------------------------
        Route::post('/auth/login', [App\Http\Controllers\Api\Auth\AuthController::class, 'login']);

        Route::middleware(['auth:sanctum', 'tenant'])
            ->group(function (): void {
                Route::get('/auth/me', [App\Http\Controllers\Api\Auth\AuthController::class, 'me']);
                Route::post('/auth/logout', [App\Http\Controllers\Api\Auth\AuthController::class, 'logout']);

                // Tenant-scoped business endpoints (10-API §7-§12, §18).
                Route::middleware(['tenant.required'])->group(function (): void {
                    // --- Patients (10-API §7) -----------------------------
                    Route::get('/patients', [PatientController::class, 'index'])
                        ->middleware('can:' . Permissions::PATIENTS_READ);
                    Route::post('/patients', [PatientController::class, 'store'])
                        ->middleware('can:' . Permissions::PATIENTS_CREATE);
                    Route::get('/patients/{id}', [PatientController::class, 'show'])
                        ->middleware('can:' . Permissions::PATIENTS_READ);
                    Route::patch('/patients/{id}', [PatientController::class, 'update'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::post('/patients/{id}/deactivate', [PatientController::class, 'deactivate'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::post('/patients/{id}/reactivate', [PatientController::class, 'reactivate'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::post('/patients/{id}/archive', [PatientController::class, 'archive'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::post('/patients/{id}/restore', [PatientController::class, 'restore'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::post('/patients/{id}/purge', [PatientController::class, 'purge'])
                        ->middleware('can:' . Permissions::PURGE_EXECUTE);

                    Route::get('/patients/{id}/identifiers', [PatientController::class, 'identifiers'])
                        ->middleware('can:' . Permissions::PATIENTS_READ);
                    Route::post('/patients/{id}/identifiers', [PatientController::class, 'storeIdentifier'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::post('/patients/{id}/identifiers/{identifierId}/rotate', [PatientController::class, 'rotateIdentifier'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::get('/patients/{id}/demographics', [PatientController::class, 'demographics'])
                        ->middleware('can:' . Permissions::PATIENTS_READ);
                    Route::post('/patients/{id}/demographics', [PatientController::class, 'storeDemographic'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::get('/patients/{id}/consents', [PatientController::class, 'consents'])
                        ->middleware('can:' . Permissions::PATIENTS_READ);
                    Route::post('/patients/{id}/consents', [PatientController::class, 'storeConsent'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::get('/patients/{id}/relations', [PatientController::class, 'relations'])
                        ->middleware('can:' . Permissions::PATIENTS_READ);
                    Route::post('/patients/{id}/relations', [PatientController::class, 'storeRelation'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);
                    Route::get('/patients/{id}/aliases', [PatientController::class, 'aliases'])
                        ->middleware('can:' . Permissions::PATIENTS_READ);
                    Route::post('/patients/{id}/aliases', [PatientController::class, 'storeAlias'])
                        ->middleware('can:' . Permissions::PATIENTS_UPDATE);

                    // --- Staff (10-API §8) --------------------------------
                    Route::get('/staff', [StaffController::class, 'index'])
                        ->middleware('can:' . Permissions::STAFF_READ);
                    Route::post('/staff', [StaffController::class, 'store'])
                        ->middleware('can:' . Permissions::STAFF_CREATE);
                    Route::get('/staff/{id}', [StaffController::class, 'show'])
                        ->middleware('can:' . Permissions::STAFF_READ);
                    Route::patch('/staff/{id}', [StaffController::class, 'update'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);
                    Route::post('/staff/{id}/deactivate', [StaffController::class, 'deactivate'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);
                    Route::post('/staff/{id}/reactivate', [StaffController::class, 'reactivate'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);
                    Route::post('/staff/{id}/archive', [StaffController::class, 'archive'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);
                    Route::post('/staff/{id}/restore', [StaffController::class, 'restore'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);
                    Route::post('/staff/{id}/purge', [StaffController::class, 'purge'])
                        ->middleware('can:' . Permissions::PURGE_EXECUTE);

                    Route::get('/staff/{id}/identifiers', [StaffController::class, 'identifiers'])
                        ->middleware('can:' . Permissions::STAFF_READ);
                    Route::post('/staff/{id}/identifiers', [StaffController::class, 'storeIdentifier'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);
                    Route::post('/staff/{id}/identifiers/{identifierId}/rotate', [StaffController::class, 'rotateIdentifier'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);
                    Route::get('/staff/{id}/credentials', [StaffController::class, 'credentials'])
                        ->middleware('can:' . Permissions::STAFF_READ);
                    Route::post('/staff/{id}/credentials', [StaffController::class, 'storeCredential'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);
                    Route::get('/staff/{id}/consents', [StaffController::class, 'consents'])
                        ->middleware('can:' . Permissions::STAFF_READ);
                    Route::post('/staff/{id}/consents', [StaffController::class, 'storeConsent'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);
                    Route::get('/staff/{id}/demographics', [StaffController::class, 'demographics'])
                        ->middleware('can:' . Permissions::STAFF_READ);
                    Route::post('/staff/{id}/demographics', [StaffController::class, 'storeDemographic'])
                        ->middleware('can:' . Permissions::STAFF_UPDATE);

                    // --- Providers (10-API §9) ----------------------------
                    Route::get('/providers', [ProviderController::class, 'index'])
                        ->middleware('can:' . Permissions::PROVIDERS_READ);
                    Route::post('/providers', [ProviderController::class, 'store'])
                        ->middleware('can:' . Permissions::PROVIDERS_CREATE);
                    Route::get('/providers/{id}', [ProviderController::class, 'show'])
                        ->middleware('can:' . Permissions::PROVIDERS_READ);
                    Route::patch('/providers/{id}', [ProviderController::class, 'update'])
                        ->middleware('can:' . Permissions::PROVIDERS_UPDATE);
                    Route::post('/providers/{id}/deactivate', [ProviderController::class, 'deactivate'])
                        ->middleware('can:' . Permissions::PROVIDERS_UPDATE);
                    Route::post('/providers/{id}/reactivate', [ProviderController::class, 'reactivate'])
                        ->middleware('can:' . Permissions::PROVIDERS_UPDATE);
                    Route::post('/providers/{id}/archive', [ProviderController::class, 'archive'])
                        ->middleware('can:' . Permissions::PROVIDERS_UPDATE);
                    Route::post('/providers/{id}/restore', [ProviderController::class, 'restore'])
                        ->middleware('can:' . Permissions::PROVIDERS_UPDATE);
                    Route::post('/providers/{id}/purge', [ProviderController::class, 'purge'])
                        ->middleware('can:' . Permissions::PURGE_EXECUTE);

                    Route::get('/providers/{id}/identifiers', [ProviderController::class, 'identifiers'])
                        ->middleware('can:' . Permissions::PROVIDERS_READ);
                    Route::post('/providers/{id}/identifiers', [ProviderController::class, 'storeIdentifier'])
                        ->middleware('can:' . Permissions::PROVIDERS_UPDATE);
                    Route::post('/providers/{id}/identifiers/{identifierId}/rotate', [ProviderController::class, 'rotateIdentifier'])
                        ->middleware('can:' . Permissions::PROVIDERS_UPDATE);
                    Route::get('/providers/{id}/credentials', [ProviderController::class, 'credentials'])
                        ->middleware('can:' . Permissions::PROVIDERS_READ);
                    Route::post('/providers/{id}/credentials', [ProviderController::class, 'storeCredential'])
                        ->middleware('can:' . Permissions::PROVIDERS_UPDATE);
                    Route::get('/providers/{id}/networks', [ProviderController::class, 'networks'])
                        ->middleware('can:' . Permissions::PROVIDERS_READ);
                    Route::post('/providers/{id}/networks', [ProviderController::class, 'storeNetwork'])
                        ->middleware('can:' . Permissions::PROVIDERS_UPDATE);

                    // --- Organizations (10-API §10) -----------------------
                    Route::get('/organizations', [OrganizationController::class, 'index'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_READ);
                    Route::post('/organizations', [OrganizationController::class, 'store'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_CREATE);
                    Route::get('/organizations/{id}', [OrganizationController::class, 'show'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_READ);
                    Route::patch('/organizations/{id}', [OrganizationController::class, 'update'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_UPDATE);
                    Route::post('/organizations/{id}/deactivate', [OrganizationController::class, 'deactivate'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_UPDATE);
                    Route::post('/organizations/{id}/reactivate', [OrganizationController::class, 'reactivate'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_UPDATE);
                    Route::post('/organizations/{id}/archive', [OrganizationController::class, 'archive'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_UPDATE);
                    Route::post('/organizations/{id}/restore', [OrganizationController::class, 'restore'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_UPDATE);
                    Route::post('/organizations/{id}/purge', [OrganizationController::class, 'purge'])
                        ->middleware('can:' . Permissions::PURGE_EXECUTE);

                    Route::get('/organizations/{id}/identifiers', [OrganizationController::class, 'identifiers'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_READ);
                    Route::post('/organizations/{id}/identifiers', [OrganizationController::class, 'storeIdentifier'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_UPDATE);
                    Route::post('/organizations/{id}/identifiers/{identifierId}/rotate', [OrganizationController::class, 'rotateIdentifier'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_UPDATE);
                    Route::get('/organizations/{id}/contacts', [OrganizationController::class, 'contacts'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_READ);
                    Route::post('/organizations/{id}/contacts', [OrganizationController::class, 'storeContact'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_UPDATE);
                    Route::get('/organizations/{id}/relationships', [OrganizationController::class, 'relationships'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_READ);
                    Route::post('/organizations/{id}/relationships', [OrganizationController::class, 'storeRelationship'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_UPDATE);

                    // --- Reference categories (10-API §11) ----------------
                    Route::get('/reference-categories', [ReferenceCategoryController::class, 'index'])
                        ->middleware('can:' . Permissions::REFERENCE_READ);
                    Route::post('/reference-categories', [ReferenceCategoryController::class, 'store'])
                        ->middleware('can:' . Permissions::REFERENCE_MANAGE);
                    Route::get('/reference-categories/{id}', [ReferenceCategoryController::class, 'show'])
                        ->middleware('can:' . Permissions::REFERENCE_READ);
                    Route::patch('/reference-categories/{id}', [ReferenceCategoryController::class, 'update'])
                        ->middleware('can:' . Permissions::REFERENCE_MANAGE);
                    Route::post('/reference-categories/{id}/deactivate', [ReferenceCategoryController::class, 'deactivate'])
                        ->middleware('can:' . Permissions::REFERENCE_MANAGE);
                    Route::post('/reference-categories/{id}/reactivate', [ReferenceCategoryController::class, 'reactivate'])
                        ->middleware('can:' . Permissions::REFERENCE_MANAGE);
                    Route::post('/reference-categories/{id}/purge', [ReferenceCategoryController::class, 'purge'])
                        ->middleware('can:' . Permissions::PURGE_EXECUTE);

                    // --- Reference values (10-API §11) --------------------
                    Route::get('/reference-values', [ReferenceValueController::class, 'index'])
                        ->middleware('can:' . Permissions::REFERENCE_READ);
                    Route::post('/reference-values', [ReferenceValueController::class, 'store'])
                        ->middleware('can:' . Permissions::REFERENCE_MANAGE);
                    Route::get('/reference-values/{id}', [ReferenceValueController::class, 'show'])
                        ->middleware('can:' . Permissions::REFERENCE_READ);
                    Route::patch('/reference-values/{id}', [ReferenceValueController::class, 'update'])
                        ->middleware('can:' . Permissions::REFERENCE_MANAGE);
                    Route::post('/reference-values/{id}/deactivate', [ReferenceValueController::class, 'deactivate'])
                        ->middleware('can:' . Permissions::REFERENCE_MANAGE);
                    Route::post('/reference-values/{id}/reactivate', [ReferenceValueController::class, 'reactivate'])
                        ->middleware('can:' . Permissions::REFERENCE_MANAGE);
                    Route::post('/reference-values/{id}/purge', [ReferenceValueController::class, 'purge'])
                        ->middleware('can:' . Permissions::PURGE_EXECUTE);

                    // --- Enterprise persons / EPI (10-API §12) ------------
                    Route::get('/enterprise-persons', [EnterprisePersonController::class, 'index'])
                        ->middleware('can:' . Permissions::MASTERDATA_READ);
                    Route::post('/enterprise-persons', [EnterprisePersonController::class, 'store']);
                    Route::get('/enterprise-persons/{id}', [EnterprisePersonController::class, 'show'])
                        ->middleware('can:' . Permissions::MASTERDATA_READ);

                    // --- Versions (10-API §18) ----------------------------
                    Route::get('/master-records/{id}/versions', [VersionController::class, 'index'])
                        ->middleware('can:' . Permissions::MASTERDATA_READ);
                    Route::get('/master-records/{id}/versions/{vid}', [VersionController::class, 'show'])
                        ->middleware('can:' . Permissions::MASTERDATA_READ);
                    Route::get('/master-records/{id}/versions/{vid}/diff', [VersionController::class, 'diff'])
                        ->middleware('can:' . Permissions::MASTERDATA_READ);

                    // --- Search (10-API §12) ------------------------------
                    Route::get('/search/patients', [SearchController::class, 'patients'])
                        ->middleware('can:' . Permissions::PATIENTS_READ);
                    Route::get('/search/staff', [SearchController::class, 'staff'])
                        ->middleware('can:' . Permissions::STAFF_READ);
                    Route::get('/search/providers', [SearchController::class, 'providers'])
                        ->middleware('can:' . Permissions::PROVIDERS_READ);
                    Route::get('/search/organizations', [SearchController::class, 'organizations'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_READ);
                    Route::get('/search/master', [SearchController::class, 'master'])
                        ->middleware('can:' . Permissions::MASTERDATA_READ);
                });
            });
    });
