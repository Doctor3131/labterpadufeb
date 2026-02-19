<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman Lab - {{ $schedule->course }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4;
            margin: 1cm;
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
            line-height: 1.15;
            font-size: 11pt;
        }
    </style>
</head>
<body class="bg-white text-black p-4 max-w-[210mm] mx-auto min-h-screen">

    <!-- Print Controls -->
    <div class="fixed top-4 right-4 gap-4 no-print flex">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print / Save PDF
        </button>
        <button onclick="window.history.back()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow-lg">
            Kembali
        </button>
    </div>

    <!-- Top Right Header -->
    <div class="text-right font-bold text-[10pt] mb-4">
        @php
            $typeLabels = [
                'perkuliahan_tetap' => 'Perkuliahan Tetap',
                'perkuliahan_tidak_tetap' => 'Perkuliahan Tidak Tetap',
                'non_perkuliahan' => 'Non-Perkuliahan',
            ];
            $label = $typeLabels[$schedule->type] ?? $schedule->type;
            $doc = $schedule->document;
        @endphp
        Peminjaman : {{ $label }}
    </div>

    <!-- Main Header -->
    <div class="text-center font-bold mb-6">
        <div class="text-[11pt]">FORM PEMINJAMAN RUANG LABORATORIUM</div>
        <div class="text-[11pt]">UNIT PENGEMBANGAN KOMPUTER FAKULTAS EKONOMIKA DAN BISNIS</div>
        <div class="text-[11pt]">UNIVERSITAS DIPONEGORO</div>
    </div>

    <!-- Content -->
    <div class="ml-4 text-[11pt]">
        <table class="w-full">
            <tr>
                <td class="w-[250px] py-1 align-top">MATA KULIAH / KEGIATAN</td>
                <td class="w-[10px] py-1 align-top">:</td>
                <td class="py-1 font-bold">{{ $schedule->course ?: '-' }}</td>
            </tr>
            @if($doc && $doc->study_program)
            <tr>
                <td class="py-1">STRATA/JURUSAN</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $doc->study_program }}</td>
            </tr>
            @endif
            <tr>
                <td class="py-1">DOSEN PENGAMPU/ INSTRUKTUR</td>
                <td class="py-1">:</td>
                <td class="py-1">
                    @if(in_array($schedule->type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']))
                        {{ $schedule->lecturer ?: '-' }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="py-1">KOORDINATOR</td>
                <td class="py-1">:</td>
                <td class="py-1">
                    @if(in_array($schedule->type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']))
                        {{ $schedule->komting ?: '-' }}
                    @else
                        {{-- For non-perkuliahan, PIC is stored in lecturer column --}}
                        {{ $schedule->lecturer ?: '-' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="py-1">NO. TELP. KOORDINATOR</td>
                <td class="py-1">:</td>
                <td class="py-1">
                    @if(in_array($schedule->type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']))
                        {{-- Perkuliahan: phone from schedules.komting_phone --}}
                        {{ $schedule->komting_phone ?: '-' }}
                    @else
                        {{-- Non-perkuliahan: phone from schedule_documents.phone_number --}}
                        {{ ($doc && $doc->phone_number) ? $doc->phone_number : '-' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="py-1 align-top">HARI, TANGGAL</td>
                <td class="py-1 align-top">:</td>
                <td class="py-1">
                    {{ $schedule->day }}@if($schedule->start_date), {{ $schedule->start_date->locale('id')->isoFormat('D MMMM Y') }}@endif
                </td>
            </tr>
            <tr>
                <td class="py-1 align-top">JAM</td>
                <td class="py-1 align-top">:</td>
                <td class="py-1">
                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} s.d. {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }} WIB
                </td>
            </tr>
            <tr>
                <td class="py-1">SOFTWARE YANG DIGUNAKAN</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ ($doc && $doc->software_needs) ? $doc->software_needs : '-' }}</td>
            </tr>
            <tr>
                <td class="py-1 align-top">LAB YANG DIGUNAKAN</td>
                <td class="py-1 align-top">:</td>
                <td class="py-1">
                    @if($schedule->lab)
                        {{ $schedule->lab->name }} (kap. {{ $schedule->lab->capacity }})
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="py-1">JUMLAH PESERTA</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $schedule->student_count ?: '-' }}</td>
            </tr>
            <tr>
                <td class="py-1">KONFIRMASI ULANG</td>
                <td class="py-1">:</td>
            </tr>
        </table>
    </div>

    <!-- Signatures Header -->
    <div class="flex justify-between mt-8 px-4 text-[11pt]">
        <div class="text-center w-[280px]">
            <div>Mengetahui</div>
        </div>
        <div class="text-center w-[220px]">
            <div>Asisten Unit Pengembangan Komputer</div>
            <div>FEB Undip</div>
        </div>
    </div>

    <!-- Signatures Space -->
    <div class="h-[60px]"></div>

    <!-- Signatures Names -->
    <div class="flex justify-between px-4 text-[11pt]">
        <div class="text-center w-[280px]">
            <div class="border-b border-black font-bold pb-1">
                {{ $schedule->lecturer ?: '____________________' }}
            </div>
            <div class="mt-1 flex gap-1">
                @if($schedule->type === 'non_perkuliahan')
                    @if($doc && $doc->nip)
                        <span>NIP.</span>
                        <span>{{ $doc->nip }}</span>
                    @elseif($doc && $doc->nim)
                        <span>NIM.</span>
                        <span>{{ $doc->nim }}</span>
                    @else
                        <span>NIM.</span>
                        <span>....................</span>
                    @endif
                @else
                    <span>NIP.</span>
                    <span>{{ ($doc && $doc->lecturer_nip) ? $doc->lecturer_nip : '....................' }}</span>
                @endif
            </div>
        </div>
        <div class="text-center w-[220px]">
             <div class="border-b border-black font-bold pb-1 text-transparent">SpaceForName</div>
             <div class="mt-1 flex gap-1">
                 <span>NIM.</span>
             </div>
        </div>
    </div>

    <!-- Rules Box -->
    <div class="mt-6 border-[1.5px] border-black rounded-[25px] p-4 relative">
        <div class="text-center font-bold text-[9pt] mb-2">Peraturan Penggunaan/Peminjaman Laboratorium</div>
        <div class="text-[8pt] text-justify px-4 leading-tight">
            <ol class="list-decimal pl-4 space-y-0.5">
                <li>Peminjaman Laboratorium UPKFEB hanya digunakan untuk kegiatan akademik fakultas EKONOMIKA DAN BISNIS universitas diponegoro</li>
                <li>Koordinator diwajibkan meninggalkan KTM kepada asisten Laboratorium sebagai jaminan</li>
                <li>Apabila kegiatan menggunakan software yang belum tersedia di Laboratorium UPKFEB, koordinator wajib memberikan software tersebut paling lambat tiga hari sebelum pelaksanaan kegiatan.</li>
                <li>Konfirmasi ulang atas kepastian jadwal paling lambat 3 hari sebelum pelaksanaan.</li>
                <li>Selama pemakaian Laboratorium Pengguna harus mematuhi peraturan Laboratorium UPKFEB dan peraturan yang berlaku.</li>
                <li>Koordinator bertanggungjawab penuh terhadap keadaan dan kondisi Laboratorium selama kegiatan.</li>
            </ol>
        </div>
        
        <div class="flex justify-end mt-4 mr-8 text-[9pt] font-bold">
            <div class="text-center">
                <div class="mb-8">TTD</div>
                <div>Ketua Lab UPKFEB</div>
            </div>
        </div>
    </div>
    
    <div class="text-[8pt] mt-2 ml-2 text-gray-500">
        Dibuat pada : {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
