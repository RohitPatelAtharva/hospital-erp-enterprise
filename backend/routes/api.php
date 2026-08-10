<?php

use App\Http\Controllers\Api\V1\MasterData\EnterprisePersonController;
use App\Http\Controllers\Api\V1\MasterData\OrganizationController;
use App\Http\Controllers\Api\V1\MasterData\PatientController;
use App\Http\Controllers\Api\V1\MasterData\ProviderController;
use App\Http\Controllers\Api\V1\MasterData\ReferenceCategoryController;
use App\Http\Controllers\Api\V1\MasterData\ReferenceValueController;
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
                    Route::post('/patients', [PatientController::class, 'store']);
                    Route::get('/patients/{id}', [PatientController::class, 'show'])
                        ->middleware('can:' . Permissions::PATIENTS_READ);

                    // --- Staff (10-API §8) --------------------------------
                    Route::get('/staff', [StaffController::class, 'index'])
                        ->middleware('can:' . Permissions::STAFF_READ);
                    Route::post('/staff', [StaffController::class, 'store']);
                    Route::get('/staff/{id}', [StaffController::class, 'show'])
                        ->middleware('can:' . Permissions::STAFF_READ);

                    // --- Providers (10-API §9) ----------------------------
                    Route::get('/providers', [ProviderController::class, 'index'])
                        ->middleware('can:' . Permissions::PROVIDERS_READ);
                    Route::post('/providers', [ProviderController::class, 'store']);
                    Route::get('/providers/{id}', [ProviderController::class, 'show'])
                        ->middleware('can:' . Permissions::PROVIDERS_READ);

                    // --- Organizations (10-API §10) -----------------------
                    Route::get('/organizations', [OrganizationController::class, 'index'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_READ);
                    Route::post('/organizations', [OrganizationController::class, 'store']);
                    Route::get('/organizations/{id}', [OrganizationController::class, 'show'])
                        ->middleware('can:' . Permissions::ORGANIZATIONS_READ);

                    // --- Reference categories (10-API §11) ----------------
                    Route::get('/reference-categories', [ReferenceCategoryController::class, 'index'])
                        ->middleware('can:' . Permissions::REFERENCE_READ);
                    Route::post('/reference-categories', [ReferenceCategoryController::class, 'store']);
                    Route::get('/reference-categories/{id}', [ReferenceCategoryController::class, 'show'])
                        ->middleware('can:' . Permissions::REFERENCE_READ);

                    // --- Reference values (10-API §11) --------------------
                    Route::get('/reference-values', [ReferenceValueController::class, 'index'])
                        ->middleware('can:' . Permissions::REFERENCE_READ);
                    Route::post('/reference-values', [ReferenceValueController::class, 'store']);
                    Route::get('/reference-values/{id}', [ReferenceValueController::class, 'show'])
                        ->middleware('can:' . Permissions::REFERENCE_READ);

                    // --- Enterprise persons / EPI (10-API §12) ------------
                    Route::get('/enterprise-persons', [EnterprisePersonController::class, 'index'])
                        ->middleware('can:' . Permissions::MASTERDATA_READ);
                    Route::post('/enterprise-persons', [EnterprisePersonController::class, 'store']);
                    Route::get('/enterprise-persons/{id}', [EnterprisePersonController::class, 'show'])
                        ->middleware('can:' . Permissions::MASTERDATA_READ);

                    // --- Versions (10-API §18) ----------------------------
                    Route::get('/master-records/{id}/versions', [VersionController::class, 'index'])
                        ->middleware('can:' . Permissions::MASTERDATA_READ);
                });
            });
    });
