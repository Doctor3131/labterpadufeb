<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BpsMasterData;
use App\Models\BpsSubData;

echo "=== BPS MASTER DATA ===\n";
$masters = BpsMasterData::orderBy('id')->get();
echo "Total: " . $masters->count() . "\n\n";

foreach ($masters as $m) {
    echo "ID: {$m->id} | {$m->name} [{$m->code}] | " . ($m->has_sub_data ? 'Multi Level' : 'Single Level') . "\n";
}

echo "\n=== BPS SUB DATA ===\n";
$subs = BpsSubData::with('master')->orderBy('master_id')->orderBy('id')->get();
echo "Total: " . $subs->count() . "\n\n";

foreach ($subs as $s) {
    $masterCode = $s->master ? $s->master->code : 'N/A';
    echo "ID: {$s->id} | Master: {$s->master_id} ({$masterCode}) | {$s->name}\n";
}
