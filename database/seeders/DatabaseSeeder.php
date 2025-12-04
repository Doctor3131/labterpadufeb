<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lab;
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
        // Create Admin User
        User::create([
            'name' => 'Admin FEB',
            'email' => 'admin@feb.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Mahasiswa Users
        User::create([
            'name' => 'Mahasiswa Test',
            'email' => 'mahasiswa@feb.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
        ]);

        // Create Labs
        Lab::create([
            'name' => 'Lab Komputer 1',
            'code' => 'LAB-K1',
            'description' => 'Laboratorium komputer dengan 40 unit PC untuk praktikum',
            'location' => 'Gedung A Lantai 2',
            'capacity' => 40,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'Lab Komputer 2',
            'code' => 'LAB-K2',
            'description' => 'Laboratorium komputer dengan 35 unit PC',
            'location' => 'Gedung A Lantai 3',
            'capacity' => 35,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'Lab Akuntansi',
            'code' => 'LAB-AKT',
            'description' => 'Laboratorium khusus untuk praktikum akuntansi',
            'location' => 'Gedung B Lantai 1',
            'capacity' => 30,
            'status' => 'occupied',
        ]);

        Lab::create([
            'name' => 'Lab Manajemen',
            'code' => 'LAB-MNJ',
            'description' => 'Laboratorium untuk praktikum manajemen dan simulasi bisnis',
            'location' => 'Gedung B Lantai 2',
            'capacity' => 25,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'Lab Multimedia',
            'code' => 'LAB-MM',
            'description' => 'Laboratorium dengan fasilitas multimedia lengkap',
            'location' => 'Gedung C Lantai 1',
            'capacity' => 30,
            'status' => 'maintenance',
        ]);
    }
}
