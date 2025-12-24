<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman Lab - {{ $booking->tracking_token }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4;
            margin: 2cm;
        }
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none;
            }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            line-height: 1.3;
        }
        .dotted-underline {
            border-bottom: 1px dotted #000;
        }
    </style>
</head>
<body class="bg-white text-black p-8 max-w-[210mm] mx-auto min-h-screen">

    <!-- Print Controls -->
    <div class="fixed top-4 right-4 gap-4 no-print flex">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print / Save PDF
        </button>
        <a href="{{ route('booking.track', $booking->tracking_token) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow-lg">
            Kembali
        </a>
    </div>

    <!-- Header -->
    <div class="text-center font-bold mb-8">
        <div class="text-[11pt] tracking-wide">PEMINJAMAN : {{ strtoupper($booking->is_recurring ? 'Perkuliahan Tetap' : ($booking->booking_type === 'non_perkuliahan' ? 'Non-Perkuliahan' : 'Perkuliahan Tidak Tetap')) }}</div>
        <br>
        <div class="text-[12pt]">FORM PEMINJAMAN RUANG LABORATORIUM</div>
        <div class="text-[12pt]">UNIT PENGEMBANGAN KOMPUTER FAKULTAS EKONOMIKA DAN BISNIS</div>
        <div class="text-[12pt]">UNIVERSITAS DIPONEGORO</div>
    </div>

    <!-- Content -->
    <div class="ml-4 text-[11pt]">
        <table class="w-full">
            <tr>
                <td class="w-[250px] py-1 valign-top">MATA KULIAH / KEGIATAN</td>
                <td class="w-[10px] py-1 valign-top">:</td>
                <td class="py-1 font-bold">{{ $booking->course_name ?: $booking->activity_name }}</td>
            </tr>
            <tr>
                <td class="py-1">STRATA/JURUSAN</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $booking->study_program }}</td>
            </tr>
            <tr>
                <td class="py-1">DOSEN PENGAMPU/ INSTRUKTUR</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $booking->lecturer_name ?: '-' }}</td>
            </tr>
            <tr>
                <td class="py-1">KOORDINATOR</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $booking->pic_name }}</td>
            </tr>
            <tr>
                <td class="py-1">NO. TELP. KOORDINATOR</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $booking->phone_number }}</td>
            </tr>
            <tr>
                <td class="py-1 valign-top">HARI, TANGGAL</td>
                <td class="py-1 valign-top">:</td>
                <td class="py-1">
                    {{ \Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('dddd') }}, {{ \Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('D MMMM Y') }}
                </td>
            </tr>
            <tr>
                <td class="py-1 valign-top">JAM</td>
                <td class="py-1 valign-top">:</td>
                <td class="py-1">
                    {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} s.d. {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }} WIB
                </td>
            </tr>
            <tr>
                <td class="py-1">SOFTWARE YANG DIGUNAKAN</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $booking->software_needs ?: '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 valign-top">LAB YANG DIGUNAKAN</td>
                <td class="py-1 valign-top">:</td>
                <td class="py-1">
                    {{ $booking->lab->name }} (kap. {{ $booking->lab->capacity }})
                </td>
            </tr>
            <tr>
                <td class="py-1">JUMLAH PESERTA</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $booking->participant_count }}</td>
            </tr>
            <tr>
                <td class="py-1">KONFIRMASI ULANG</td>
                <td class="py-1">:</td>
                <td class="py-1">______________________</td>
            </tr>
        </table>
    </div>

    <!-- Signatures Header -->
    <div class="flex justify-between mt-12 px-8 text-[11pt]">
        <div class="text-center w-[250px]">
            <div>Mengetahui</div>
        </div>
        <div class="text-center w-[300px]">
            <div>Asisten Unit Pengembangan Komputer</div>
            <div>FEB Undip</div>
        </div>
    </div>

    <!-- Signatures Space -->
    <div class="h-[80px]"></div>

    <!-- Signatures Names -->
    <div class="flex justify-between px-8 text-[11pt]">
        <div class="text-center w-[250px]">
            <div class="border-b border-black font-bold pb-1">{{ $booking->lecturer_name ?: '____________________' }}</div>
            <div class="mt-1 flex justify-between">
                <span>NIP.</span>
                <span>{{ $booking->lecturer_nip ?: '....................' }}</span>
            </div>
        </div>
        <div class="text-center w-[300px]">
             <div class="border-b border-black font-bold pb-1 text-transparent">SpaceForName</div>
             <div class="mt-1 flex justify-center">
                 <span>NIM.</span>
             </div>
        </div>
    </div>

    <!-- Rules Box -->
    <div class="mt-12 border border-black rounded-[20px] p-6 text-[10pt]">
        <div class="text-center font-bold mb-4">Peraturan Penggunaan/Peminjaman Laboratorium</div>
        <ol class="list-none space-y-1 pl-4">
            <li class="pl-4 -indent-4">1. Peminjaman Laboratorium UPKFEB hanya digunakan untuk kegiatan akademik fakultas EKONOMIKA DAN BISNIS universitas diponegoro</li>
            <li class="pl-4 -indent-4">2. Koordinator diwajibkan meninggalkan KTM kepada asisten Laboratorium sebagai jaminan</li>
            <li class="pl-4 -indent-4">3. Apabila kegiatan menggunakan software yang belum tersedia di Laboratorium UPKFEB, koordinator wajib memberikan software tersebut paling lambat tiga hari sebelum pelaksanaan kegiatan.</li>
            <li class="pl-4 -indent-4">4. Konfirmasi ulang atas kepastian jadwal paling lambat 3 hari sebelum pelaksanaan.</li>
            <li class="pl-4 -indent-4">5. Selama pemakaian Laboratorium Pengguna harus mematuhi peraturan Laboratorium UPKFEB dan peraturan yang berlaku.</li>
            <li class="pl-4 -indent-4">6. Koordinator bertanggungjawab penuh terhadap keadaan dan kondisi Laboratorium selama kegiatan.</li>
        </ol>
        <div class="mt-8 text-right font-bold pr-12">
            TTD
            <br><br><br>
            Ketua Lab UPKFEB
        </div>
    </div>
    
    <div class="text-[9pt] mt-4 ml-4">
        Dibuat pada : {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <script>
        // Auto print based on url param or just let user click
        // window.print(); 
    </script>
</body>
</html>
