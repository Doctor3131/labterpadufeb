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
            ['EL. 301', '07:00', '09:30', 'Statistik Bisnis - Kelas A', 'Dr. Ahyar Yuniawan, S.E., M.Si.', 'Reza Akmal Wibowo', 23],
            ['EL. 306', '07:00', '09:30', 'Statistik Bisnis - Kelas B', 'Nana Yuriant Setyawan, S.E., MBA', 'Mufa Nur Falah', 23],
            ['EL. 301', '10:00', '12:30', 'Statistik Bisnis II', 'Dr. Ahyar Yuniawan, S.E., M.Si.', 'Nur Adila', 42],
            ['EL. 306', '10:00', '13:00', 'Matematika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', null, null],
            ['EL. 306', '13:00', '15:30', 'Statistika Bisnis', 'Danes Quirira Octavio, S.E., M.Sc.', 'Christian Dennis Wibowo', 45],
            ['EL. 301', '13:00', '15:30', 'Praktikum Pengauditan', 'Dr. Totok Dewayanto, S.E., M.Si., Akt.', null, null],
            ['EL. 307', '13:00', '15:30', 'Kecerdasan Buatan', 'Ardiaz Ajie Aryandika S.Kom., MBA', null, 40],
            ['EL. 301', '15:40', '17:20', 'Internet of Things', 'Moh. Najikhul Fajri S.E., M.SE.', 'Raden Hanif Khairullah Arladiningrat', 50],
        ];

        // Semester dates: Dec 2025 - Jun 2026 (Genap 2025/2026)
        $semesterStart = '2025-12-01';
        $semesterEnd = '2026-06-30';

        foreach ($seninSchedules as $schedule) {
            $lab = Lab::where('name', $schedule[0])->first();
            if ($lab) {
                Schedule::create([
                    'lab_id' => $lab->id,
                    'day' => 'Senin',
                    'start_date' => $semesterStart,
                    'end_date' => $semesterEnd,
                    'start_time' => $schedule[1],
                    'end_time' => $schedule[2],
                    'course' => $schedule[3],
                    'lecturer' => $schedule[4],
                    'komting' => $schedule[5], // Add komting to regular schedules
                    'student_count' => $schedule[6], // Add student_count to regular schedules
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
                    'start_date' => $semesterStart,
                    'end_date' => $semesterEnd,
                    'start_time' => $schedule[1],
                    'end_time' => $schedule[2],
                    'course' => $schedule[3],
                    'lecturer' => $schedule[4],
                    'komting' => $schedule[5],
                    'student_count' => $schedule[6],
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
                    'start_date' => $semesterStart,
                    'end_date' => $semesterEnd,
                    'start_time' => $schedule[1],
                    'end_time' => $schedule[2],
                    'course' => $schedule[3],
                    'lecturer' => $schedule[4],
                    'komting' => $schedule[5],
                    'student_count' => $schedule[6],
                    'type' => 'regular',
                ]);
            }
        }

        // Sample Bookings (One-time events) for testing dynamic schedules
        $booking1 = \App\Models\Booking::create([
            'booking_type' => 'non_perkuliahan',
            'nama_peminjam' => 'Ahmad Faizal',
            'program_studi' => 'Manajemen',
            'nim' => '12010122140001',
            'no_telpon' => '081234567890',
            'alamat' => 'Semarang',
            'lab_id' => Lab::where('name', 'EL. 309')->first()->id,
            'tanggal' => '2025-12-23',
            'day' => 'Selasa',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'jumlah_peserta' => 30,
            'jenis_kegiatan' => 'Workshop',
            'nama_kegiatan' => 'Workshop Data Analytics with Python',
            'kebutuhan_peralatan' => 'Proyektor, Sound System',
            'tracking_token' => bin2hex(random_bytes(16)),
            'status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now(),
        ]);

        // Create schedule for booking 1 using timezone-aware date handling
        $booking1Date = \Carbon\Carbon::parse($booking1->tanggal)->timezone('Asia/Jakarta');
        Schedule::create([
            'lab_id' => $booking1->lab_id,
            'day' => $booking1->day,
            'start_date' => $booking1Date->toDateString(), // Y-m-d format only
            'end_date' => $booking1Date->toDateString(),
            'start_time' => $booking1->start_time,
            'end_time' => $booking1->end_time,
            'course' => $booking1->nama_kegiatan,
            'lecturer' => $booking1->nama_peminjam,
            'type' => 'booking_onetime',
            'booking_id' => $booking1->id,
        ]);

        $booking2 = \App\Models\Booking::create([
            'booking_type' => 'perkuliahan_tidak_tetap',
            'nama_peminjam' => 'Dr. Budi Santoso',
            'program_studi' => 'Akuntansi',
            'nim' => '199001011234',
            'no_telpon' => '081298765432',
            'lab_id' => Lab::where('name', 'EL. 301')->first()->id,
            'tanggal' => '2025-12-19',
            'day' => 'Jumat',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'jumlah_peserta' => 45,
            'mata_kuliah' => 'Sistem Informasi Manajemen',
            'dosen_pengampu' => 'Dr. Budi Santoso, S.E., M.M.',
            'nip_dosen' => '199001011234',
            'software_digunakan' => 'Microsoft Excel, Power BI',
            'tracking_token' => bin2hex(random_bytes(16)),
            'status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now(),
        ]);

        // Create schedule for booking 2 using timezone-aware date handling
        $booking2Date = \Carbon\Carbon::parse($booking2->tanggal)->timezone('Asia/Jakarta');
        Schedule::create([
            'lab_id' => $booking2->lab_id,
            'day' => $booking2->day,
            'start_date' => $booking2Date->toDateString(), // Y-m-d format only
            'end_date' => $booking2Date->toDateString(),
            'start_time' => $booking2->start_time,
            'end_time' => $booking2->end_time,
            'course' => $booking2->mata_kuliah,
            'lecturer' => $booking2->dosen_pengampu,
            'type' => 'booking_onetime',
            'booking_id' => $booking2->id,
        ]);
    }
}
