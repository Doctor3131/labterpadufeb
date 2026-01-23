<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lab;
use App\Models\Schedule;
use App\Helpers\DayHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Asset Type Codes first (for inventory)
        $this->call(AssetTypeCodeSeeder::class);

        // Create Super Admin User
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@feb.ac.id',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // Create Admin/Aslab Users
        User::create([
            'name' => 'Admin Aslab 1',
            'email' => 'admin@feb.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        
        User::create([
            'name' => 'Admin Aslab 2',
            'email' => 'aslab@feb.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Labs
        Lab::create([
            'name' => 'EL. 301',
            'description' => 'Laboratorium komputer lantai 3',
            'capacity' => 50,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'EL. 306',
            'description' => 'Laboratorium komputer lantai 3',
            'capacity' => 45,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'EL. 307',
            'description' => 'Laboratorium komputer lantai 3',
            'capacity' => 40,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'EL. 309',
            'description' => 'Laboratorium komputer lantai 3',
            'capacity' => 30,
            'status' => 'available',
        ]);

        // Remove EL. 3 01 creation as requested
        
        // Seed Approved Bookings WITH corresponding Schedules
        // Using fixed weekday dates to avoid Sunday (not in schedules ENUM)
        
    }
}
