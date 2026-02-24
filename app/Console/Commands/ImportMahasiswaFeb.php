<?php

namespace App\Console\Commands;

use App\Models\MahasiswaFeb;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMahasiswaFeb extends Command
{
    protected $signature = 'import:mahasiswa {file? : Path to CSV file (default: mahasiswa_feb.csv in project root)}';
    protected $description = 'Import data mahasiswa FEB from CSV file into mahasiswa_feb table';

    public function handle(): int
    {
        $file = $this->argument('file') ?? base_path('mahasiswa_feb.csv');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            $this->info('Please place the CSV file (nim,nama,prodi) at the specified location.');
            return Command::FAILURE;
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->error("Cannot open file: {$file}");
            return Command::FAILURE;
        }

        // Count total lines for progress bar
        $totalLines = 0;
        while (fgets($handle) !== false) {
            $totalLines++;
        }
        rewind($handle);

        // Skip header row
        $header = fgetcsv($handle);
        if ($header === false) {
            $this->error('CSV file is empty.');
            fclose($handle);
            return Command::FAILURE;
        }

        $totalLines--; // Subtract header
        $this->info("Importing {$totalLines} mahasiswa records...");
        $bar = $this->output->createProgressBar($totalLines);
        $bar->start();

        $imported = 0;
        $skipped = 0;
        $batch = [];
        $batchSize = 500;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty($row) || empty(trim($row[0] ?? ''))) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $nim = trim($row[0] ?? '');
            $nama = trim($row[1] ?? '');
            $prodi = trim($row[2] ?? '');

            if (empty($nim) || empty($nama)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $batch[] = [
                'nim' => $nim,
                'nama' => $nama,
                'prodi' => $prodi,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                DB::table('mahasiswa_feb')->upsert($batch, ['nim'], ['nama', 'prodi', 'updated_at']);
                $imported += count($batch);
                $batch = [];
            }

            $bar->advance();
        }

        // Insert remaining batch
        if (!empty($batch)) {
            DB::table('mahasiswa_feb')->upsert($batch, ['nim'], ['nama', 'prodi', 'updated_at']);
            $imported += count($batch);
        }

        fclose($handle);
        $bar->finish();

        $this->newLine(2);
        $this->info("✅ Import complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Imported/Updated', $imported],
                ['Skipped (empty)', $skipped],
                ['Total in DB', MahasiswaFeb::count()],
            ]
        );

        return Command::SUCCESS;
    }
}
