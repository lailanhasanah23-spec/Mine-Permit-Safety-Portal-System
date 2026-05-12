<?php

include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->handle(Request::capture());

use App\Support\Legacy\LegacyRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

$isAdminAuthenticated = false;
$categories = LegacyRepository::portalGetCategories();
$formsByCategory = LegacyRepository::portalGetFormsByCategory($categories, $isAdminAuthenticated);

$cat3All = $formsByCategory[3] ?? [];
$cat4All = $formsByCategory[4] ?? [];

$col3Forms = [];
foreach (array_merge($cat4All, $cat3All) as $f) {
    if ($f['purpose'] === 'monitoring' && (int) ($f['id'] ?? 0) !== 10) {
        $col3Forms[] = $f;
    }
}

echo "Column 3 Forms:\n";
foreach ($col3Forms as $f) {
    echo "- [ID: {$f['id']}] {$f['title']}\n";
}
