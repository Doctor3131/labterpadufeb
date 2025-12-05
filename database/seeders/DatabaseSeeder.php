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
            'name' => 'EL. 301',
            'code' => 'EL-301',
            'description' => 'Laboratorium komputer lantai 3',
            'location' => 'Gedung E Lantai 3',
            'capacity' => 50,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'EL. 306',
            'code' => 'EL-306',
            'description' => 'Laboratorium komputer lantai 3',
            'location' => 'Gedung E Lantai 3',
            'capacity' => 45,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'EL. 307',
            'code' => 'EL-307',
            'description' => 'Laboratorium komputer lantai 3',
            'location' => 'Gedung E Lantai 3',
            'capacity' => 40,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'EL. 309',
            'code' => 'EL-309',
            'description' => 'Laboratorium komputer lantai 3',
            'location' => 'Gedung E Lantai 3',
            'capacity' => 30,
            'status' => 'available',
        ]);

        Lab::create([
            'name' => 'EL. 3 01',
            'code' => 'EL-3-01',
            'description' => 'Laboratorium komputer lantai 3',
            'location' => 'Gedung E Lantai 3',
            'capacity' => 60,
            'status' => 'available',
        ]);

        // Seed Schedules - SENIN
        $seninSchedules = [
            ['EL. 301', '07:00', '09:30', 'Statistik Bisnis', 'Dr. Ahyar Yuniawan, S.E., M.Si.', 'Reza Akmal Wibowo', 23],
            ['EL. 301', '07:00', '09:30', 'Statistik Bisnis', 'Nana Yuriant Setyawan, S.E., MBA', 'Mufa Nur Falah', 23],
            ['EL. 301', '10:00', '12:30', 'Statistik Bisnis II', 'Dr. Ahyar Yuniawan, S.E., M.Si.', 'Nur Adila', 42],
            ['EL. 306', '10:00', '13:00', 'Matematika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', null, null],
            ['EL. 306', '13:00', '15:30', 'Statistika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', 'Christian Dennis Wibowo', 45],
            ['EL. 301', '13:00', '15:30', 'Praktikum Pengauditan', 'Dr. Totok Dewayanto, S.E., M.Si., Akt.', null, null],
            ['EL. 307', '13:00', '15:30', 'Kecerdasan Buatan', 'ardiaz', null, 40],
            ['EL. 301', '15:40', '17:20', 'Internet of Things', 'Moh. Najikhul Fajri S.E., M.SE.', 'Raden Hanif Khairullah Arladiningrat', 50],
            ['EL. 301', '15:40', '17:20', 'Internet of Things', 'Moh. Najikhul Fajri S.E., M.SE.', 'Raden Hanif Khairullah Arladiningrat', 50],
        ];

        foreach ($seninSchedules as $schedule) {
            $lab = Lab::where('name', $schedule[0])->first();
            if ($lab) {
                Schedule::create([
                    'lab_id' => $lab->id,
                    'day' => 'Senin',
                    'start_time' => $schedule[1],
                    'end_time' => $schedule[2],
                    'course' => $schedule[3],
                    'lecturer' => $schedule[4],
                    'komting' => $schedule[5] ?? null,
                    'student_count' => $schedule[6] ?? null,
                    'type' => 'regular',
                ]);
            }
        }

        // Seed Schedules - RABU
        $rabuSchedules = [
            ['EL. 309', '07:30', '09:30', 'Desain UI/UX', 'Ardiaz Ajie Aryandika S.Kom., MBA', 'Ahmad Samy Samudra', 24],
            ['EL. 301', '10:00', '12:30', 'Pengkodean dan Pemrograman - A', 'Dr. Totok Dewayanto, S.E., M.Si., Akt.', 'Dinar Fenisia Haloho', 11],
            ['EL. 301', '13:00', '15:30', 'Matematika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', null, 45],
            ['EL. 309', '13:00', '15:50', 'Desain UI/UX', 'Ardiaz Ajie Aryandika S.Kom., MBA', 'Muhammad Bilal Arrafi', 30],
            ['EL. 3 01', '13:00', '15:40', 'Enterprise Resource Planning (ERP) - B', 'Dr. Totok Dewayanto, S.E., M.Si., Akt.', 'Faiz Fainuz Ikbar', 61],
        ];

        foreach ($rabuSchedules as $schedule) {
            $lab = Lab::where('name', $schedule[0])->first();
            if ($lab) {
                Schedule::create([
                    'lab_id' => $lab->id,
                    'day' => 'Rabu',
                    'start_time' => $schedule[1],
                    'end_time' => $schedule[2],
                    'course' => $schedule[3],
                    'lecturer' => $schedule[4],
                    'komting' => $schedule[5] ?? null,
                    'student_count' => $schedule[6] ?? null,
                    'type' => 'regular',
                ]);
            }
        }

        // Seed Schedules - KAMIS
        $kamisSchedules = [
            ['EL. 309', '10:00', '12:30', 'Statistika Bisnis', 'I Made Sukresna S.E., M.Si., Ph.D.', 'Dikha Angelino Ivan', 21],
            ['EL. 306', '10:00', '12:30', 'Matematika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', null, null],
            ['EL. 301', '10:00', '12:30', 'Sistem Informasi Akuntansi - E', 'Dr. Totok Dewayanto, S.E., M.Si., Akt.', 'Rimanda Gantarianto', null],
            ['EL. 301', '13:00', '15:30', 'Sistem Informasi Akuntansi', 'Mutiara Tresna Parasetya, S.E., M.Si., Ak.', 'Dwi Febrina Rizkita Vina Putri', null],
            ['EL. 306', '13:00', '15:40', 'Matematika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', null, null],
            ['EL. 309', '13:00', '15:50', 'Desain UI/UX', 'Ardiaz Ajie Aryandika S.Kom., MBA', 'M. F. Hylmi Ramadhani', 30],
        ];

        foreach ($kamisSchedules as $schedule) {
            $lab = Lab::where('name', $schedule[0])->first();
            if ($lab) {
                Schedule::create([
                    'lab_id' => $lab->id,
                    'day' => 'Kamis',
                    'start_time' => $schedule[1],
                    'end_time' => $schedule[2],
                    'course' => $schedule[3],
                    'lecturer' => $schedule[4],
                    'komting' => $schedule[5] ?? null,
                    'student_count' => $schedule[6] ?? null,
                    'type' => 'regular',
                ]);
            }
        }
    }
}
