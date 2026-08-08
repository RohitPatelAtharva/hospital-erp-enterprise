<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    */

    'version' => env('API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Document Metadata
    |--------------------------------------------------------------------------
    */

    'title' => 'Hospital ERP Enterprise — API',
    'description' => 'Enterprise API for the Hospital ERP platform. Contract-first per docs/11-API-STANDARDS.md. Foundation endpoints only in Phase 1; Master Data business endpoints are added in later phases.',

    /*
    |--------------------------------------------------------------------------
    | Servers
    |--------------------------------------------------------------------------
    */

    'servers' => [
        ['url' => env('APP_URL', 'http://localhost'), 'description' => 'Default server'],
    ],

];
