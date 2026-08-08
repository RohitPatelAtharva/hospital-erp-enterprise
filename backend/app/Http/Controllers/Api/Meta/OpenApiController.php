<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Meta;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Serves a minimal OpenAPI 3.0.3 document (docs/11-API-STANDARDS.md §4).
 *
 * Phase 1 describes only the foundation endpoints and security schemes; Master
 * Data business paths are appended in later phases. This is documentation, not
 * an executable contract validator.
 */
final class OpenApiController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'openapi' => '3.0.3',
            'info' => [
                'title' => config('openapi.title'),
                'description' => config('openapi.description'),
                'version' => config('openapi.version'),
            ],
            'servers' => config('openapi.servers'),
            'paths' => $this->paths(),
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Sanctum token',
                    ],
                ],
            ],
            'security' => [
                ['bearerAuth' => []],
            ],
        ]);
    }

    private function paths(): array
    {
        return [
            '/api/v1/health' => [
                'get' => [
                    'summary' => 'Application health check',
                    'security' => [],
                    'responses' => ['200' => ['description' => 'OK']],
                ],
            ],
            '/api/v1/auth/login' => [
                'post' => [
                    'summary' => 'Authenticate and obtain a token',
                    'security' => [],
                    'responses' => ['200' => ['description' => 'Token issued']],
                ],
            ],
            '/api/v1/auth/me' => [
                'get' => [
                    'summary' => 'Current principal',
                    'responses' => ['200' => ['description' => 'Principal']],
                ],
            ],
            '/api/v1/auth/logout' => [
                'post' => [
                    'summary' => 'Revoke the current token',
                    'responses' => ['204' => ['description' => 'No content']],
                ],
            ],
        ];
    }
}
