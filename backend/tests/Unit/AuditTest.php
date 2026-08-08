<?php

namespace Tests\Unit;

use App\Audit\AuditEvent;
use App\Audit\AuditRecorder;
use App\Audit\AuditRecord;
use App\Audit\Stores\LogAuditStore;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;
use Tests\Unit\Fakes\FakeAuditStore;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_recorder_populates_actor_tenant_and_correlation(): void
    {
        $store = new FakeAuditStore();
        $recorder = new AuditRecorder($store);

        $user = User::factory()->create();
        Auth::login($user);
        TenantContext::setContext($user->tenant_id, $user->facility_id);
        app()->instance('request-correlation-id', 'corr-1');

        $recorder->record(AuditEvent::CREATED, 'patient', 'p-1', 'create');

        $this->assertCount(1, $store->records);
        $record = $store->records[0];
        $this->assertSame('created', $record->event);
        $this->assertSame('patient', $record->entity);
        $this->assertSame((string) $user->id, $record->actorId);
        $this->assertSame($user->tenant_id, $record->tenantId);
        $this->assertSame('corr-1', $record->correlationId);
    }

    public function test_recorder_redacts_phi_keys_from_context(): void
    {
        $store = new FakeAuditStore();
        $recorder = new AuditRecorder($store);

        $recorder->record(AuditEvent::UPDATED, 'patient', 'p-1', 'update', [
            'name' => 'safe',
            'ssn' => '123-45-6789',
            'contact_value' => 'phone-number',
        ]);

        $context = $store->records[0]->context;
        $this->assertSame('safe', $context['name']);
        $this->assertSame('[REDACTED]', $context['ssn']);
        $this->assertSame('[REDACTED]', $context['contact_value']);
    }

    public function test_log_store_writes_to_the_audit_channel(): void
    {
        $logger = Mockery::mock(\Psr\Log\LoggerInterface::class);
        $logger->shouldReceive('info')
            ->once()
            ->with('audit.event', Mockery::on(fn (array $context): bool => ($context['event'] ?? null) === 'created'));

        Log::shouldReceive('channel')->once()->with('audit')->andReturn($logger);

        $store = new LogAuditStore();
        $store->record(new AuditRecord('created', 'patient', 'p-1', tenantId: 'tenant-a'));
    }
}
