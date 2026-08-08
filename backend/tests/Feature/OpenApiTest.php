<?php

namespace Tests\Feature;

use Tests\TestCase;

class OpenApiTest extends TestCase
{
    public function test_openapi_document_is_served(): void
    {
        $this->getJson('/api/v1/openapi.json')
            ->assertOk()
            ->assertJsonPath('openapi', '3.0.3')
            ->assertJsonPath('info.title', config('openapi.title'));
    }

    public function test_openapi_describes_foundation_endpoints(): void
    {
        $this->getJson('/api/v1/openapi.json')
            ->assertOk()
            ->assertJsonStructure([
                'paths' => [
                    '/api/v1/health',
                    '/api/v1/auth/login',
                    '/api/v1/auth/me',
                    '/api/v1/auth/logout',
                ],
            ]);
    }
}
