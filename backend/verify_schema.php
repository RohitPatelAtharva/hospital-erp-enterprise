<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$framework = [
    'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs',
    'migrations', 'password_reset_tokens', 'personal_access_tokens', 'sessions', 'users',
];

$rows = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
$tables = array_map(static fn ($r) => $r->tablename, $rows);
$md = array_values(array_diff($tables, $framework));
sort($md);

$list = "'" . implode("','", $md) . "'";

$out = [];

$out['canonical tables'] = count($md) . '/109';

$out['RLS enabled'] = DB::selectOne(
    "SELECT count(*)::int c FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
     WHERE n.nspname = 'public' AND c.relrowsecurity = TRUE AND c.relname IN ($list)"
)->c . '/109';

$out['RLS FORCE'] = DB::selectOne(
    "SELECT count(*)::int c FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
     WHERE n.nspname = 'public' AND c.relforcerowsecurity = TRUE AND c.relname IN ($list)"
)->c . '/109';

$out['FKs'] = DB::selectOne(
    "SELECT count(*)::int c FROM pg_constraint con JOIN pg_class c ON c.oid = con.conrelid
     JOIN pg_namespace n ON n.oid = c.relnamespace
     WHERE con.contype = 'f' AND n.nspname = 'public' AND c.relname IN ($list)"
)->c . '/112';

$out['unique constraints'] = DB::selectOne(
    "SELECT count(*)::int c FROM pg_constraint con JOIN pg_class c ON c.oid = con.conrelid
     JOIN pg_namespace n ON n.oid = c.relnamespace
     WHERE con.contype = 'u' AND n.nspname = 'public' AND c.relname IN ($list)"
)->c . '/80';

$out['secondary (ix_%) indexes'] = DB::selectOne(
    "SELECT count(*)::int c FROM pg_indexes WHERE schemaname = 'public' AND indexname LIKE 'ix\_%' AND tablename IN ($list)"
)->c . ' (expect 100)';

$out['ix_address_entity present'] = DB::selectOne(
    "SELECT count(*)::int c FROM pg_indexes WHERE schemaname = 'public' AND indexname = 'ix_address_entity'"
)->c . ' (expect 0)';

$out['entity_address_link table present'] = DB::selectOne(
    "SELECT count(*)::int c FROM pg_tables WHERE schemaname = 'public' AND tablename = 'entity_address_link'"
)->c . ' (expect 0)';

$out['address.entity_id column present'] = DB::selectOne(
    "SELECT count(*)::int c FROM information_schema.columns
     WHERE table_schema = 'public' AND table_name = 'address' AND column_name = 'entity_id'"
)->c . ' (expect 0)';

foreach ($out as $label => $value) {
    echo str_pad($label, 34) . ": $value\n";
}
