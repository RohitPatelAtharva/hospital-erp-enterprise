<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_reports_ok(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.checks.app', 'ok')
            ->assertJsonPath('data.checks.database', 'ok');
    }

    public function test_health_endpoint_uses_the_standard_envelope(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['status', 'checks'],
                'meta' => ['service'],
            ]);
    }
}
