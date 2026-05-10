<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class BpsDataSeeder extends Seeder
{
    /**
     * Run the database seeds for BPS catalog safely.
     *
     * This seeder is intentionally non-destructive and delegates to
     * the production-safe sync command.
     */
    public function run(): void
    {
        Artisan::call('bps:sync-catalog', [
            '--keep-missing' => false,
        ]);

        $this->command?->line(Artisan::output());
    }
}
