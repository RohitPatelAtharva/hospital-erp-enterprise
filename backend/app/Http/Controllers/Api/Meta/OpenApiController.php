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

            // --- Master Data aggregate lists (10-API §7-§12) --------------
            '/api/v1/patients' => $this->collection('List patients'),
            '/api/v1/staff' => $this->collection('List staff'),
            '/api/v1/providers' => $this->collection('List providers'),
            '/api/v1/organizations' => $this->collection('List organizations'),
            '/api/v1/reference-categories' => $this->collection('List reference categories'),
            '/api/v1/reference-values' => $this->collection('List reference values'),
            '/api/v1/enterprise-persons' => $this->collection('List enterprise persons'),
        ];
    }

    /**
     * A read-only list operation (GET) for a Master Data resource.
     *
     * @return array{get: array{summary: string, responses: array{200: array{description: string}}}}
     */
    private function collection(string $summary): array
    {
        return [
            'get' => [
                'summary' => $summary,
                'responses' => ['200' => ['description' => 'Paginated list']],
            ],
        ];
    }
}
