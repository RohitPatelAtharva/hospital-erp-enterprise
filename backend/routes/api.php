<?php

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

                // Tenant-scoped foundation guard (business routes in later phases).
                Route::middleware(['tenant.required'])->group(function (): void {
                    // Phase 2+ business endpoints are mounted here.
                });
            });
    });
