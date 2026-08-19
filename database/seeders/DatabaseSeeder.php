<?php

namespace Database\Seeders;

use App\Models\Lab;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Idempotent: safe to run repeatedly; the deploy flow guards it with
     * `db:seed` only when `bookings` is empty so dump-restored data stays
     * authoritative. Admin credentials come from env (ADMIN_NAME /
     * ADMIN_EMAIL / ADMIN_PASSWORD) and fall back to demo values.
     */
    public function run(): void
    {
        $adminName = env('ADMIN_NAME', 'Super Admin');
        $adminEmail = env('ADMIN_EMAIL', 'superadmin@feb.ac.id');
        $adminPassword = env('ADMIN_PASSWORD', 'password');

        // Create Super Admin User
        $superAdmin = User::updateOrCreate(
            ['email' => $adminEmail],
            ['name' => $adminName, 'password' => Hash::make($adminPassword)]
        );
        $superAdmin->role = 'super_admin';
        $superAdmin->save();

        // Create Admin/Aslab Users
        $adminOne = User::updateOrCreate(
            ['email' => 'admin@feb.ac.id'],
            ['name' => 'Admin Aslab 1', 'password' => Hash::make('password')]
        );
        $adminOne->role = 'admin';
        $adminOne->save();

        $adminTwo = User::updateOrCreate(
            ['email' => 'aslab@feb.ac.id'],
            ['name' => 'Admin Aslab 2', 'password' => Hash::make('password')]
        );
        $adminTwo->role = 'admin';
        $adminTwo->save();

        // Create Labs
        $labs = [
            ['name' => 'EL. 301', 'description' => 'Laboratorium komputer lantai 3', 'capacity' => 50, 'status' => 'available'],
            ['name' => 'EL. 306', 'description' => 'Laboratorium komputer lantai 3', 'capacity' => 45, 'status' => 'available'],
            ['name' => 'EL. 307', 'description' => 'Laboratorium komputer lantai 3', 'capacity' => 40, 'status' => 'available'],
            ['name' => 'EL. 309', 'description' => 'Laboratorium komputer lantai 3', 'capacity' => 30, 'status' => 'available'],
        ];

        foreach ($labs as $lab) {
            Lab::updateOrCreate(['name' => $lab['name']], $lab);
        }

        // Seed asset type codes for structured-tag inventory tracking
        $this->call(AssetTypeCodeSeeder::class);
    }
}
