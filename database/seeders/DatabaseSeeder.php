<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lab;
use App\Models\Schedule;
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

        // Seed Schedules - SENIN
        $seninSchedules = [
            ['07:00', '09:30', 'EL. 301', 'Statistik Bisnis', 'Dr. Ahyar Yuniawan, S.E., M.Si.', 'Reza Akmal Wibowo', 23],
            ['07:00', '09:30', 'EL. 301', 'Statistik Bisnis', 'Nana Yuriant Setyawan, S.E., MBA', 'Mufa Nur Falah', 23],
            ['10:00', '12:30', 'EL. 301', 'Statistik Bisnis II', 'Dr. Ahyar Yuniawan, S.E., M.Si.', 'Nur Adila', 42],
            ['10:00', '13:00', 'EL. 306', 'Matematika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', null, null],
            ['13:00', '15:30', 'EL. 306', 'Statistika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', 'Christian Dennis Wibowo', 45],
            ['13:00', '15:30', 'EL. 301', 'Praktikum Pengauditan', 'Dr. Totok Dewayanto, S.E., M.Si., Akt.', null, null],
            ['13:00', '15:30', 'EL. 307', 'Kecerdasan Buatan', 'ardiaz', null, 40],
            ['15:40', '17:20', 'EL. 301', 'Internet of Things', 'Moh. Najikhul Fajri S.E., M.SE.', 'Raden Hanif Khairullah Arladiningrat', 50],
            ['15:40', '17:20', 'EL. 301', 'Internet of Things', 'Moh. Najikhul Fajri S.E., M.SE.', 'Raden Hanif Khairullah Arladiningrat', 50],
        ];

        foreach ($seninSchedules as $schedule) {
            Schedule::create([
                'day' => 'Senin',
                'start_time' => $schedule[0],
                'end_time' => $schedule[1],
                'room' => $schedule[2],
                'course' => $schedule[3],
                'lecturer' => $schedule[4],
                'komting' => $schedule[5] ?? null,
                'student_count' => $schedule[6] ?? null,
                'type' => 'regular',
            ]);
        }

        // Seed Schedules - RABU
        $rabuSchedules = [
            ['07:30', '09:30', 'EL. 309', 'Desain UI/UX', 'Ardiaz Ajie Aryandika S.Kom., MBA', 'Ahmad Samy Samudra', 24],
            ['10:00', '12:30', 'EL. 301', 'Pengkodean dan Pemrograman - A', 'Dr. Totok Dewayanto, S.E., M.Si., Akt.', 'Dinar Fenisia Haloho', 11],
            ['13:00', '15:30', 'EL. 301', 'Matematika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', null, 45],
            ['13:00', '15:50', 'EL. 309', 'Desain UI/UX', 'Ardiaz Ajie Aryandika S.Kom., MBA', 'Muhammad Bilal Arrafi', 30],
            ['13:00', '15:40', 'EL. 3 01', 'Enterprise Resource Planning (ERP) - B', 'Dr. Totok Dewayanto, S.E., M.Si., Akt.', 'Faiz Fainuz Ikbar', 61],
        ];

        foreach ($rabuSchedules as $schedule) {
            Schedule::create([
                'day' => 'Rabu',
                'start_time' => $schedule[0],
                'end_time' => $schedule[1],
                'room' => $schedule[2],
                'course' => $schedule[3],
                'lecturer' => $schedule[4],
                'komting' => $schedule[5] ?? null,
                'student_count' => $schedule[6] ?? null,
                'type' => 'regular',
            ]);
        }

        // Seed Schedules - KAMIS
        $kamisSchedules = [
            ['10:00', '12:30', 'EL. 309', 'Statistika Bisnis', 'I Made Sukresna S.E., M.Si., Ph.D.', 'Dikha Angelino Ivan', 21],
            ['10:00', '12:30', 'EL. 306', 'Matematika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', null, null],
            ['10:00', '12:30', 'EL. 301', 'Sistem Informasi Akuntansi - E', 'Dr. Totok Dewayanto, S.E., M.Si., Akt.', 'Rimanda Gantarianto', null],
            ['13:00', '15:30', 'EL. 301', 'Sistem Informasi Akuntansi', 'Mutiara Tresna Parasetya, S.E., M.Si., Ak.', 'Dwi Febrina Rizkita Vina Putri', null],
            ['13:00', '15:40', 'EL. 306', 'Matematika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', null, null],
            ['13:00', '15:50', 'EL. 309', 'Desain UI/UX', 'Ardiaz Ajie Aryandika S.Kom., MBA', 'M. F. Hylmi Ramadhani', 30],
        ];

        foreach ($kamisSchedules as $schedule) {
            Schedule::create([
                'day' => 'Kamis',
                'start_time' => $schedule[0],
                'end_time' => $schedule[1],
                'room' => $schedule[2],
                'course' => $schedule[3],
                'lecturer' => $schedule[4],
                'komting' => $schedule[5] ?? null,
                'student_count' => $schedule[6] ?? null,
                'type' => 'regular',
            ]);
        }
    }
}
