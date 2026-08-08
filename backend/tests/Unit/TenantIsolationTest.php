<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private BaseModel $scopedModel;

    protected function setUp(): void
    {
        parent::setUp();

        // A scratch tenant-scoped model backed by an existing table so the
        // TenantScope (BaseModel) can be exercised without inventing a table.
        $this->scopedModel = new class extends BaseModel
        {
            protected $table = 'users';
        };
    }

    public function test_queries_are_limited_to_the_active_tenant(): void
    {
        User::factory()->create(['tenant_id' => 'tenant-a', 'facility_id' => 'facility-a', 'email' => 'a@x.com']);
        User::factory()->create(['tenant_id' => 'tenant-b', 'facility_id' => 'facility-b', 'email' => 'b@x.com']);

        TenantContext::setContext('tenant-a', 'facility-a');

        $this->assertSame(['a@x.com'], $this->scopedModel->newQuery()->pluck('email')->all());
    }

    public function test_switching_tenant_changes_the_result_set(): void
    {
        User::factory()->create(['tenant_id' => 'tenant-a', 'facility_id' => 'facility-a', 'email' => 'a@x.com']);
        User::factory()->create(['tenant_id' => 'tenant-b', 'facility_id' => 'facility-b', 'email' => 'b@x.com']);

        TenantContext::setContext('tenant-b', 'facility-b');

        $this->assertSame(['b@x.com'], $this->scopedModel->newQuery()->pluck('email')->all());
    }

    public function test_system_context_applies_no_tenant_filter(): void
    {
        User::factory()->create(['tenant_id' => 'tenant-a', 'facility_id' => 'facility-a', 'email' => 'a@x.com']);
        User::factory()->create(['tenant_id' => 'tenant-b', 'facility_id' => 'facility-b', 'email' => 'b@x.com']);

        TenantContext::clear();

        $this->assertCount(2, $this->scopedModel->newQuery()->pluck('email')->all());
    }
}
