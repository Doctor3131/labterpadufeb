<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;

class UpdateItemCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:update-categories {--force : Update all items even if they have categories}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update kategori barang berdasarkan nama barang';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating item categories...');
        
        // Auto-assign kategori untuk STRUCTURED_TAG items berdasarkan asset_type_code
        $structuredTagMap = [
            'H3' => 'PC',
            'I2' => 'TV',
            'BRK' => 'Bracket TV',
            'J1' => 'Speaker',
            'O1' => 'Laptop',
            'L1' => 'Printer',
            'P' => 'Tablet',
        ];
        
        // Mapping nama barang ke kategori untuk non-STRUCTURED_TAG
        $categoryMappings = [
            'Kabel HDMI' => 'Kabel',
            'Kabel VGA' => 'Kabel',
            'Kabel' => 'Kabel',
            'Keyboard' => 'Keyboard',
            'Mouse' => 'Mouse',
            'Router' => 'Router',
            'Switch' => 'Switch',
            'Speaker' => 'Speaker',
            'Webcam' => 'Webcam',
            'Laptop' => 'Laptop',
            'Printer' => 'Printer',
            'Scanner' => 'Scanner',
            'TV' => 'TV',
            'Tablet' => 'Tablet',
            'Monitor' => 'Monitor',
            'HP Pavilion' => 'Monitor',
            'PC AIO' => 'PC',
            'PC' => 'PC',
            'Proyektor' => 'Proyektor',
            'AC' => 'AC',
            'Meja' => 'Meja',
            'Kursi' => 'Kursi',
        ];
        
        // Jika --force, update semua. Jika tidak, hanya yang belum ada kategori
        if ($this->option('force')) {
            $items = \App\Models\Item::with('assetTypeCode')->get();
            $this->warn('Force mode: Updating all items...');
        } else {
            $items = \App\Models\Item::with('assetTypeCode')->whereNull('category')->orWhere('category', '')->get();
        }
        
        $updated = 0;
        
        foreach ($items as $item) {
            $categoryFound = null;
            
            // Cek apakah STRUCTURED_TAG dengan asset_type_code
            if ($item->assetTypeCode && isset($structuredTagMap[$item->assetTypeCode->code])) {
                $categoryFound = $structuredTagMap[$item->assetTypeCode->code];
                $this->line("✓ {$item->name} → {$categoryFound} (dari kode tipe {$item->assetTypeCode->code})");
            } else {
                // Cek berdasarkan nama barang
                $itemName = $item->name;
                
                foreach ($categoryMappings as $keyword => $category) {
                    if (stripos($itemName, $keyword) !== false) {
                        $categoryFound = $category;
                        break;
                    }
                }
                
                if ($categoryFound) {
                    $this->line("✓ {$item->name} → {$categoryFound}");
                } else {
                    $this->warn("✗ {$item->name} → (tidak ditemukan kategori yang cocok)");
                }
            }
            
            if ($categoryFound) {
                $item->category = $categoryFound;
                $item->save();
                $updated++;
            }
        }
        
        $this->info("\nTotal: {$updated} barang berhasil diupdate kategorinya.");
        
        return Command::SUCCESS;
    }
}
