<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ApplicationBootTest extends TestCase
{
    public function test_application_boots(): void
    {
        $this->assertTrue(app()->isBooted());
        $this->assertNotEmpty(config('app.name'));
    }

    public function test_database_connection_is_available(): void
    {
        DB::connection()->getPdo();

        $this->assertTrue(true);
    }

    public function test_cache_is_available(): void
    {
        Cache::put('boot.check', 'ok');

        $this->assertSame('ok', Cache::get('boot.check'));
    }
}
