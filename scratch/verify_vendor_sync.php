<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$group = DB::table('internal_company_groups')->where('group_name', 'SAPKON Vendor')->first();
if (! $group) {
    echo "group_missing\n";
    exit(1);
}

$count = DB::table('internal_companies')->where('group_id', $group->id)->count();
$nullPwd = DB::table('internal_companies')->where('group_id', $group->id)->whereNull('password_hash')->count();
$rows = DB::table('internal_companies')->where('group_id', $group->id)->orderBy('company_name')->pluck('company_name')->toArray();

echo "group_id={$group->id}\n";
echo "count={$count}\n";
echo "null_password={$nullPwd}\n";
echo 'companies='.implode(' | ', $rows)."\n";
