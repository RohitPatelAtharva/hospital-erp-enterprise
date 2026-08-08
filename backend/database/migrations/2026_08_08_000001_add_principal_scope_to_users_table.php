<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add principal scope (tenant/facility + roles) to the auth store.
     *
     * This extends the Laravel stock `users` table (auth infrastructure) so the
     * tenant-context and authorization foundations can be enforced for an
     * authenticated principal. The authoritative identity model is owned by the
     * IAM module in a later phase; this table is the Phase-1 auth store only and
     * does not touch the canonical Master Data schema (04-Database-Tables.md).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('tenant_id', 64)->nullable()->after('id');
            $table->string('facility_id', 64)->nullable()->after('tenant_id');
            $table->json('roles')->nullable()->after('password');

            $table->index(['tenant_id'], 'idx_users_tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('idx_users_tenant_id');
            $table->dropColumn(['roles', 'facility_id', 'tenant_id']);
        });
    }
};
