<?php

namespace App\Console\Commands;

use App\Models\BpsMasterData;
use App\Models\BpsSubData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBpsCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bps:sync-catalog
                            {--dry-run : Show planned changes without writing to database}
                            {--keep-missing : Keep existing sub-data active even if not in latest catalog}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync BPS master/sub-data catalog safely for production (non-destructive)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $catalog = $this->catalog();
        $dryRun = (bool) $this->option('dry-run');
        $keepMissing = (bool) $this->option('keep-missing');

        $this->info('Starting BPS catalog sync...');
        if ($dryRun) {
            $this->warn('DRY RUN mode: no data will be changed.');
        }

        $summary = [
            'master_created' => 0,
            'master_updated' => 0,
            'sub_created' => 0,
            'sub_updated' => 0,
            'sub_reactivated' => 0,
            'sub_deactivated' => 0,
        ];

        $sync = function () use ($catalog, $dryRun, $keepMissing, &$summary): void {
            foreach ($catalog as $code => $masterPayload) {
                $master = BpsMasterData::where('code', $code)->first();
                $isNewMaster = $master === null;

                if ($isNewMaster) {
                    $master = new BpsMasterData(['code' => $code]);
                    $summary['master_created']++;
                }

                $masterChanges = [
                    'name' => $masterPayload['name'],
                    'description' => $masterPayload['description'],
                    'is_active' => true,
                    'has_sub_data' => true,
                ];

                if (! $dryRun) {
                    $master->fill($masterChanges);
                    $master->save();
                }

                if (! $isNewMaster) {
                    $summary['master_updated']++;
                }

                $targetNames = [];
                foreach ($masterPayload['sub_data'] as $subName) {
                    $targetNames[] = $subName;

                    $sub = BpsSubData::where('master_id', $master->id)
                        ->where('name', $subName)
                        ->first();

                    if ($sub === null) {
                        $summary['sub_created']++;

                        if (! $dryRun) {
                            BpsSubData::create([
                                'master_id' => $master->id,
                                'name' => $subName,
                                'description' => null,
                                'is_active' => true,
                            ]);
                        }

                        continue;
                    }

                    $shouldReactivate = ! $sub->is_active;
                    if ($shouldReactivate) {
                        $summary['sub_reactivated']++;
                    }

                    $summary['sub_updated']++;

                    if (! $dryRun) {
                        $sub->update([
                            'description' => $sub->description,
                            'is_active' => true,
                        ]);
                    }
                }

                if ($keepMissing) {
                    continue;
                }

                $missingSubData = BpsSubData::where('master_id', $master->id)
                    ->whereNotIn('name', $targetNames)
                    ->where('is_active', true)
                    ->get(['id']);

                $toDeactivate = $missingSubData->count();
                $summary['sub_deactivated'] += $toDeactivate;

                if (! $dryRun && $toDeactivate > 0) {
                    BpsSubData::whereIn('id', $missingSubData->pluck('id'))
                        ->update(['is_active' => false]);
                }
            }
        };

        if ($dryRun) {
            DB::beginTransaction();
            try {
                $sync();
                DB::rollBack();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error('Dry run failed: '.$e->getMessage());

                return self::FAILURE;
            }
        } else {
            try {
                DB::transaction($sync);
            } catch (\Throwable $e) {
                $this->error('Sync failed: '.$e->getMessage());

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->table(['Metric', 'Count'], [
            ['Master created', $summary['master_created']],
            ['Master updated', $summary['master_updated']],
            ['Sub-data created', $summary['sub_created']],
            ['Sub-data updated', $summary['sub_updated']],
            ['Sub-data reactivated', $summary['sub_reactivated']],
            ['Sub-data deactivated', $summary['sub_deactivated']],
        ]);

        $this->newLine();
        $this->line('Safety notes:');
        $this->line('- Existing requests are preserved (no delete on master/sub/request tables).');
        $this->line('- Legacy single-level masters (SPAK2024, ECOM2024, ECOM2023) are left untouched.');
        $this->line('- Use --keep-missing to keep older sub-data active in UI.');

        $this->newLine();
        $this->info($dryRun ? 'Dry run completed successfully.' : 'BPS catalog synced successfully.');

        return self::SUCCESS;
    }

    /**
     * Latest target catalog map.
     *
     * @return array<string, array{name: string, description: string, sub_data: array<int, string>}>
     */
    private function catalog(): array
    {
        return [
            'PODES' => [
                'name' => 'Potensi Desa',
                'description' => 'Data Potensi Desa',
                'sub_data' => [
                    'Data Potensi Desa (PODES) Desa 2021',
                    'Data Potensi Desa (PODES) Kabupaten 2021',
                    'Data Potensi Desa (PODES) Kecamatan 2021',
                    'Pendataan Potensi Desa 2024 (Kecamatan)',
                    'Pendataan Potensi Desa 2024 (Desa)',
                    'Pendataan Potensi Desa 2024 (Daftar Infrastruktur Desa)',
                    'Pendataan Potensi Desa 2024 (Daftar Desa Terluar)',
                    'Pendataan Potensi Desa 2024 (Daftar Desa Terdepan)',
                    'Pendataan Potensi Desa 2024 (Daftar Desa Pulau-Pulau Kecil Terluar)',
                ],
            ],
            'STPIM' => [
                'name' => 'STPIM - Survei Tahunan Perusahaan Industri Manufaktur',
                'description' => 'Survei Tahunan Perusahaan Industri Manufaktur',
                'sub_data' => [
                    'Survei Tahunan Perusahaan Industri Manufaktur 2017',
                    'Survei Tahunan Perusahaan Industri Manufaktur 2018',
                    'Survei Tahunan Perusahaan Industri Manufaktur 2019',
                ],
            ],
            'SUSENAS' => [
                'name' => 'SUSENAS - Survei Sosial Ekonomi Nasional',
                'description' => 'Survei Sosial Ekonomi Nasional',
                'sub_data' => [
                    'Survei Sosial Ekonomi Nasional 2023 Maret',
                    'Survei Sosial Ekonomi Nasional 2023 Maret KOR',
                    'Survei Sosial Ekonomi Nasional 2024 September (MSBP)',
                    'Survei Sosial Ekonomi Nasional 2024 Maret (KOR)',
                    'Survei Sosial Ekonomi Nasional 2024 Maret (Modul Konsumsi dan Pengeluaran Variabel Terpilih)',
                ],
            ],
            'SAKERNAS' => [
                'name' => 'SAKERNAS - Survei Angkatan Kerja Nasional',
                'description' => 'Survei Angkatan Kerja Nasional',
                'sub_data' => [
                    'Survei Angkatan Kerja Nasional 2021 Agustus',
                    'Survei Angkatan Kerja Nasional 2022 Agustus',
                    'Survei Angkatan Kerja Nasional 2024 Agustus',
                    'Survei Angkatan Kerja Nasional 2023 Agustus',
                ],
            ],
            'KOMUTER' => [
                'name' => 'Survei Komuter',
                'description' => 'Survei Komuter',
                'sub_data' => [
                    'Survei Komuter Jabodetabek 2014',
                    'Survei Komuter Jabodetabek 2019',
                    'Survei Komuter Jabodetabek 2023',
                    'Survei Komuter Sarbagita 2023',
                    'Survei Komuter Patungraya Agung 2023',
                    'Survei Komuter Mamminasata 2023',
                    'Survei Komuter Banjarbakula 2023',
                ],
            ],
            'IMK' => [
                'name' => 'Survei Industri Mikro dan Kecil',
                'description' => 'Survei Industri Mikro dan Kecil',
                'sub_data' => [
                    'Survei Industri Mikro Dan Kecil 2019 KBLI 2 Digit (Nasional)',
                    'Survei Industri Mikro dan Kecil 2022 KBLI 2 Digit (Nasional)',
                    'Survei Industri Mikro dan Kecil 2023 KBLI 2 Digit (Nasional)',
                ],
            ],
            'PETA' => [
                'name' => 'Peta Indonesia',
                'description' => 'Peta Indonesia',
                'sub_data' => [
                    'Peta Indonesia per Desa 2023',
                    'Peta Indonesia per Desa 2024',
                ],
            ],
            'LAINNYA' => [
                'name' => 'Lain-lain',
                'description' => 'Data BPS lainnya',
                'sub_data' => [
                    'Survei E-Commerce 2023',
                    'Survei Usaha atau Perusahaan E-Commerce 2024',
                    'Survei Perilaku Anti Korupsi 2024',
                ],
            ],
        ];
    }
}
