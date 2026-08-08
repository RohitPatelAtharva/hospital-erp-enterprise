<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiVersionTest extends TestCase
{
    public function test_unsupported_api_version_is_rejected(): void
    {
        $this->withHeader('X-Api-Version', 'v99')
            ->getJson('/api/v1/health')
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'UNSUPPORTED_API_VERSION');
    }

    public function test_supported_api_version_is_accepted(): void
    {
        $this->withHeader('X-Api-Version', 'v1')
            ->getJson('/api/v1/health')
            ->assertOk();
    }

    public function test_default_version_is_v1(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk();
    }
}
