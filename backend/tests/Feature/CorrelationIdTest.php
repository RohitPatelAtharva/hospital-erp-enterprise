<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorrelationIdTest extends TestCase
{
    public function test_response_echoes_a_correlation_id(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertHeader('X-Correlation-Id');
    }

    public function test_inbound_correlation_id_is_reused(): void
    {
        $this->withHeader('X-Correlation-Id', 'trace-123')
            ->getJson('/api/v1/health')
            ->assertHeader('X-Correlation-Id', 'trace-123');
    }

    public function test_errors_include_the_correlation_id(): void
    {
        $this->withHeader('X-Correlation-Id', 'trace-456')
            ->getJson('/api/v1/health')
            ->assertOk();

        // Error path: unsupported version echoes the same correlation id.
        $this->withHeader('X-Correlation-Id', 'trace-456')
            ->withHeader('X-Api-Version', 'v9')
            ->getJson('/api/v1/health')
            ->assertStatus(400)
            ->assertJsonPath('error.correlationId', 'trace-456');
    }
}
