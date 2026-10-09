<?php

namespace Tests\Feature;

use App\Enums\TrackingModeEnum;
use App\Models\AssetTypeCode;
use App\Models\BpsMasterData;
use App\Models\BpsSubData;
use App\Models\Item;
use App\Models\MahasiswaFeb;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_import_creates_updates_and_skips_invalid_csv_rows(): void
    {
        MahasiswaFeb::create([
            'nim' => '12345678901234',
            'nama' => 'Nama Lama',
            'prodi' => 'Prodi Lama',
        ]);
        $csv = $this->temporaryCsv(<<<'CSV'
            nim,nama,prodi
            12345678901234,Nama Baru,Manajemen
            23456789012345,Mahasiswa Baru,Akuntansi
            ,Baris Tanpa NIM,Ekonomi
            34567890123456,,Ekonomi

            CSV);

        try {
            $this->artisan('import:mahasiswa', ['file' => $csv])
                ->assertExitCode(Command::SUCCESS);
        } finally {
            @unlink($csv);
        }

        $this->assertDatabaseCount('mahasiswa_feb', 2);
        $this->assertDatabaseHas('mahasiswa_feb', [
            'nim' => '12345678901234',
            'nama' => 'Nama Baru',
            'prodi' => 'Manajemen',
        ]);
        $this->assertDatabaseHas('mahasiswa_feb', [
            'nim' => '23456789012345',
            'nama' => 'Mahasiswa Baru',
            'prodi' => 'Akuntansi',
        ]);
    }

    public function test_student_import_reports_a_missing_file_without_changing_existing_data(): void
    {
        MahasiswaFeb::create([
            'nim' => '12345678901234',
            'nama' => 'Mahasiswa Tetap',
            'prodi' => 'Manajemen',
        ]);

        $this->artisan('import:mahasiswa', ['file' => '/tmp/file-mahasiswa-yang-tidak-ada.csv'])
            ->expectsOutputToContain('File not found')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseCount('mahasiswa_feb', 1);
        $this->assertDatabaseHas('mahasiswa_feb', ['nim' => '12345678901234']);
    }

    public function test_bps_catalog_dry_run_performs_no_writes(): void
    {
        $this->artisan('bps:sync-catalog', ['--dry-run' => true])
            ->expectsOutputToContain('DRY RUN')
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseCount('bps_master_data', 0);
        $this->assertDatabaseCount('bps_sub_data', 0);
    }

    public function test_bps_catalog_sync_creates_targets_and_deactivates_missing_entries(): void
    {
        $master = BpsMasterData::create([
            'name' => 'Nama Lama',
            'code' => 'PODES',
            'description' => 'Deskripsi lama',
            'is_active' => false,
            'has_sub_data' => true,
        ]);
        $missing = BpsSubData::create([
            'master_id' => $master->id,
            'name' => 'Data PODES Warisan',
            'is_active' => true,
        ]);

        $this->artisan('bps:sync-catalog')->assertExitCode(Command::SUCCESS);

        $master->refresh();
        $this->assertSame('Potensi Desa', $master->name);
        $this->assertTrue($master->is_active);
        $this->assertFalse($missing->fresh()->is_active);
        $this->assertDatabaseHas('bps_sub_data', [
            'master_id' => $master->id,
            'name' => 'Data Potensi Desa (PODES) Desa 2021',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('bps_master_data', ['code' => 'SUSENAS']);
    }

    public function test_bps_catalog_keep_missing_preserves_legacy_active_entries(): void
    {
        $master = BpsMasterData::create([
            'name' => 'Potensi Desa',
            'code' => 'PODES',
            'is_active' => true,
            'has_sub_data' => true,
        ]);
        $legacy = BpsSubData::create([
            'master_id' => $master->id,
            'name' => 'Data PODES Warisan',
            'is_active' => true,
        ]);

        $this->artisan('bps:sync-catalog', ['--keep-missing' => true])
            ->assertExitCode(Command::SUCCESS);

        $this->assertTrue($legacy->fresh()->is_active);
    }

    public function test_category_update_respects_existing_values_unless_forced(): void
    {
        $typeCode = AssetTypeCode::create([
            'code' => 'O1',
            'name' => 'Laptop',
            'default_tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
            'is_borrowable' => true,
        ]);
        $codedItem = Item::create([
            'name' => 'Notebook Inventaris',
            'asset_type_code_id' => $typeCode->id,
            'tracking_mode' => TrackingModeEnum::STRUCTURED_TAG,
        ]);
        $namedItem = Item::create([
            'name' => 'Kabel HDMI Premium',
            'tracking_mode' => TrackingModeEnum::AGGREGATE,
            'category' => 'Kategori Manual',
        ]);

        $this->artisan('items:update-categories')->assertExitCode(Command::SUCCESS);
        $this->assertSame('Laptop', $codedItem->fresh()->category);
        $this->assertSame('Kategori Manual', $namedItem->fresh()->category);

        $this->artisan('items:update-categories', ['--force' => true])->assertExitCode(Command::SUCCESS);
        $this->assertSame('Kabel', $namedItem->fresh()->category);
    }

    private function temporaryCsv(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mahasiswa-import-');
        if ($path === false) {
            throw new \RuntimeException('Unable to create temporary CSV fixture.');
        }

        file_put_contents($path, $contents);

        return $path;
    }
}
