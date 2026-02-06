<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CURRENT STATE ===\n\n";

// Show current master data
echo "Master Data:\n";
$masters = DB::table('bps_master_data')->orderBy('id')->get();
foreach ($masters as $m) {
    $subCount = DB::table('bps_sub_data')->where('master_id', $m->id)->count();
    echo "  {$m->id}. {$m->name} ({$m->code}) - has_sub_data: " . ($m->has_sub_data ? 'true' : 'false') . " - Sub data: {$subCount}\n";
}

echo "\nSub Data:\n";
$subs = DB::table('bps_sub_data')->orderBy('master_id')->orderBy('id')->get();
foreach ($subs as $s) {
    echo "  - [{$s->master_id}] {$s->name} ({$s->code})\n";
}

echo "\n=== SEEDING DATA ===\n\n";

// Get master IDs by code
$masterIds = [];
foreach ($masters as $m) {
    $masterIds[$m->code] = $m->id;
}

// Sub data definitions based on user's screenshots
$subDataDefinitions = [
    'PODES' => [
        ['name' => 'Podes 2011', 'code' => 'PODES2011'],
        ['name' => 'Podes 2014', 'code' => 'PODES2014'],
        ['name' => 'Podes 2018', 'code' => 'PODES2018'],
        ['name' => 'Podes 2021', 'code' => 'PODES2021'],
        ['name' => 'Podes 2024', 'code' => 'PODES2024'],
    ],
    'STPIM' => [
        ['name' => 'IBS 2017', 'code' => 'IBS2017'],
        ['name' => 'IBS 2018', 'code' => 'IBS2018'],
        ['name' => 'IBS 2019', 'code' => 'IBS2019'],
        ['name' => 'IBS 2020', 'code' => 'IBS2020'],
        ['name' => 'IBS 2021', 'code' => 'IBS2021'],
        ['name' => 'IBS 2022', 'code' => 'IBS2022'],
    ],
    'SUSENAS' => [
        ['name' => 'SUSENAS Maret 2017', 'code' => 'SUS-MAR2017'],
        ['name' => 'SUSENAS Maret 2018', 'code' => 'SUS-MAR2018'],
        ['name' => 'SUSENAS Maret 2019', 'code' => 'SUS-MAR2019'],
        ['name' => 'SUSENAS Maret 2020', 'code' => 'SUS-MAR2020'],
        ['name' => 'SUSENAS Maret 2021', 'code' => 'SUS-MAR2021'],
        ['name' => 'SUSENAS Maret 2022', 'code' => 'SUS-MAR2022'],
        ['name' => 'SUSENAS Maret 2023', 'code' => 'SUS-MAR2023'],
        ['name' => 'SUSENAS September 2017', 'code' => 'SUS-SEP2017'],
        ['name' => 'SUSENAS September 2018', 'code' => 'SUS-SEP2018'],
        ['name' => 'SUSENAS September 2019', 'code' => 'SUS-SEP2019'],
        ['name' => 'SUSENAS September 2020', 'code' => 'SUS-SEP2020'],
        ['name' => 'SUSENAS September 2021', 'code' => 'SUS-SEP2021'],
        ['name' => 'SUSENAS September 2022', 'code' => 'SUS-SEP2022'],
        ['name' => 'SUSENAS September 2023', 'code' => 'SUS-SEP2023'],
    ],
    'SAKERNAS' => [
        ['name' => 'Sakernas Februari 2017', 'code' => 'SAK-FEB2017'],
        ['name' => 'Sakernas Februari 2018', 'code' => 'SAK-FEB2018'],
        ['name' => 'Sakernas Februari 2019', 'code' => 'SAK-FEB2019'],
        ['name' => 'Sakernas Februari 2020', 'code' => 'SAK-FEB2020'],
        ['name' => 'Sakernas Februari 2021', 'code' => 'SAK-FEB2021'],
        ['name' => 'Sakernas Februari 2022', 'code' => 'SAK-FEB2022'],
        ['name' => 'Sakernas Februari 2023', 'code' => 'SAK-FEB2023'],
        ['name' => 'Sakernas Agustus 2017', 'code' => 'SAK-AGU2017'],
        ['name' => 'Sakernas Agustus 2018', 'code' => 'SAK-AGU2018'],
        ['name' => 'Sakernas Agustus 2019', 'code' => 'SAK-AGU2019'],
        ['name' => 'Sakernas Agustus 2020', 'code' => 'SAK-AGU2020'],
        ['name' => 'Sakernas Agustus 2021', 'code' => 'SAK-AGU2021'],
        ['name' => 'Sakernas Agustus 2022', 'code' => 'SAK-AGU2022'],
        ['name' => 'Sakernas Agustus 2023', 'code' => 'SAK-AGU2023'],
    ],
    'KOMUTER' => [
        ['name' => 'Survei Komuter 2017', 'code' => 'KOM2017'],
        ['name' => 'Survei Komuter 2019', 'code' => 'KOM2019'],
        ['name' => 'Survei Komuter 2023', 'code' => 'KOM2023'],
    ],
    'IMK' => [
        ['name' => 'Survei Industri Mikro dan Kecil 2017', 'code' => 'IMK2017'],
        ['name' => 'Survei Industri Mikro dan Kecil 2018', 'code' => 'IMK2018'],
        ['name' => 'Survei Industri Mikro dan Kecil 2019', 'code' => 'IMK2019'],
        ['name' => 'Survei Industri Mikro dan Kecil 2020', 'code' => 'IMK2020'],
        ['name' => 'Survei Industri Mikro dan Kecil 2021', 'code' => 'IMK2021'],
        ['name' => 'Survei Industri Mikro dan Kecil 2022', 'code' => 'IMK2022'],
        ['name' => 'Survei Industri Mikro dan Kecil 2023', 'code' => 'IMK2023'],
    ],
    'PETA' => [
        ['name' => 'Peta Indonesia', 'code' => 'PETA-ID'],
    ],
];

$insertedCount = 0;
$skippedCount = 0;

foreach ($subDataDefinitions as $masterCode => $subItems) {
    if (!isset($masterIds[$masterCode])) {
        echo "Warning: Master data '{$masterCode}' not found, skipping...\n";
        continue;
    }
    
    $masterId = $masterIds[$masterCode];
    
    foreach ($subItems as $sub) {
        // Check if already exists
        $exists = DB::table('bps_sub_data')
            ->where('master_id', $masterId)
            ->where('code', $sub['code'])
            ->exists();
            
        if ($exists) {
            $skippedCount++;
            continue;
        }
        
        DB::table('bps_sub_data')->insert([
            'master_id' => $masterId,
            'name' => $sub['name'],
            'code' => $sub['code'],
            'description' => $sub['name'],
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $insertedCount++;
        echo "  Inserted: [{$masterCode}] {$sub['name']}\n";
    }
}

echo "\nInserted: {$insertedCount}, Skipped (already exists): {$skippedCount}\n";

echo "\n=== FINAL STATE ===\n\n";

// Show final state
echo "Master Data:\n";
$masters = DB::table('bps_master_data')->orderBy('id')->get();
foreach ($masters as $m) {
    $subCount = DB::table('bps_sub_data')->where('master_id', $m->id)->count();
    echo "  {$m->id}. {$m->name} ({$m->code}) - has_sub_data: " . ($m->has_sub_data ? 'true' : 'false') . " - Sub data: {$subCount}\n";
}

echo "\nSub Data per Master:\n";
foreach ($masters as $m) {
    if ($m->has_sub_data) {
        $subs = DB::table('bps_sub_data')->where('master_id', $m->id)->orderBy('name')->get();
        if ($subs->count() > 0) {
            echo "\n  {$m->name}:\n";
            foreach ($subs as $s) {
                echo "    - {$s->name} ({$s->code})\n";
            }
        }
    }
}

echo "\nDone!\n";
