<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tracking Peminjaman - Lab Terpadu FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl font-bold text-yellow-600">Lab Terpadu</span>
                    <span class="text-xl text-gray-700">FEB UNDIP</span>
                </div>
                <a href="{{ route('landing') }}" class="text-gray-600 hover:text-gray-800 font-medium">
                    ← Kembali ke Beranda
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-12 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-3">Tracking Peminjaman</h1>
            <p class="text-gray-600">Detail dan status peminjaman laboratorium Anda</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Status Badge -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6 text-center">
            <div class="mb-4">
                @if($booking->status === 'pending')
                    <div class="inline-flex items-center px-6 py-3 rounded-full text-lg font-bold bg-yellow-100 text-yellow-800">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Menunggu Persetujuan
                    </div>
                @elseif($booking->status === 'approved')
                    <div class="inline-flex items-center px-6 py-3 rounded-full text-lg font-bold bg-green-100 text-green-800">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Disetujui
                    </div>
                @else
                    <div class="inline-flex items-center px-6 py-3 rounded-full text-lg font-bold bg-red-100 text-red-800">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Ditolak
                    </div>
                @endif
            </div>

            @if($booking->status === 'pending')
                <p class="text-gray-600 mb-4">Permintaan Anda sedang diproses oleh admin. Anda akan menerima notifikasi via email.</p>
            @elseif($booking->status === 'approved')
                <p class="text-green-700 font-semibold mb-4"> Peminjaman Anda telah disetujui.</p>
                <p class="text-gray-600">Silakan datang sesuai jadwal yang telah ditentukan.</p>
            @else
                <p class="text-red-700 font-semibold mb-2">Mohon maaf, peminjaman Anda ditolak.</p>
                @if($booking->rejection_reason)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4 text-left">
                        <strong class="text-red-800">Alasan Penolakan:</strong>
                        <p class="text-red-700 mt-2">{{ $booking->rejection_reason }}</p>
                    </div>
                @endif
            @endif
        </div>

        <!-- Booking Details -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Detail Peminjaman</h2>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Tipe Peminjaman</label>
                        <p class="font-semibold text-gray-800">
                            @if($booking->booking_type === 'perkuliahan_tetap')
                                Perkuliahan Tetap
                            @elseif($booking->booking_type === 'perkuliahan_tidak_tetap')
                                Perkuliahan Tidak Tetap
                            @else
                                Non-Perkuliahan
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Laboratorium</label>
                        <p class="font-semibold text-gray-800">{{ $booking->lab->name }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Tanggal</label>
                        <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Waktu</label>
                        <p class="font-semibold text-gray-800">{{ $booking->start_time }} - {{ $booking->end_time }} WIB</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Jumlah Peserta</label>
                        <p class="font-semibold text-gray-800">{{ $booking->participant_count }} orang</p>
                    </div>

                    <div>
                        <label class="text-sm text-gray-600">Status Recurring</label>
                        <p class="font-semibold text-gray-800">
                            @if($booking->is_recurring)
                                <span class="text-green-600">Ya (Setiap Minggu)</span>
                            @else
                                <span class="text-blue-600">Tidak (Sekali Saja)</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="border-t pt-4 mt-4">
                    <h3 class="font-bold text-gray-800 mb-3">Data Peminjam</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Nama</label>
                            <p class="font-semibold text-gray-800">{{ $booking->pic_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">NIM</label>
                            <p class="font-semibold text-gray-800">{{ $booking->nim }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Program Studi</label>
                            <p class="font-semibold text-gray-800">{{ $booking->study_program }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">No. Telepon</label>
                            <p class="font-semibold text-gray-800">{{ $booking->phone_number }}</p>
                        </div>
                    </div>
                </div>

                @if($booking->isPerkuliahan())
                    <div class="border-t pt-4 mt-4">
                        <h3 class="font-bold text-gray-800 mb-3">Detail Perkuliahan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Mata Kuliah</label>
                                <p class="font-semibold text-gray-800">{{ $booking->course_name }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Dosen Pengampu</label>
                                <p class="font-semibold text-gray-800">{{ $booking->lecturer_name }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($booking->isNonPerkuliahan())
                    <div class="border-t pt-4 mt-4">
                        <h3 class="font-bold text-gray-800 mb-3">Detail Kegiatan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Nama Kegiatan</label>
                                <p class="font-semibold text-gray-800">{{ $booking->activity_name }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Jenis Kegiatan</label>
                                <p class="font-semibold text-gray-800">{{ $booking->activity_type }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="border-t pt-4 mt-4">
                    <div class="text-sm text-gray-600">
                        <p><strong>Diajukan:</strong> {{ $booking->created_at->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB</p>
                        @if($booking->updated_at != $booking->created_at)
                            <p><strong>Terakhir Diupdate:</strong> {{ $booking->updated_at->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @if($booking->status === 'pending')
                <form action="{{ route('booking.cancel', $booking->tracking_token) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan peminjaman ini?')">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto bg-red-500 hover:bg-red-600 text-white px-8 py-3 rounded-lg font-semibold shadow-lg transition-colors">
                        Batalkan Peminjaman
                    </button>
                </form>
            @endif

            @if($booking->status === 'rejected')
                <a href="{{ route('booking.create') }}" class="w-full sm:w-auto bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-semibold text-center shadow-lg transition-colors">
                    Ajukan Peminjaman Baru
                </a>
            @endif

            <a href="{{ route('landing') }}" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold text-center shadow-lg transition-colors">
                Kembali ke Beranda
            </a>
        </div>

        <!-- Info Box -->
        @if($booking->status === 'pending')
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="font-bold text-blue-800 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi Penting
                </h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Simpan link halaman ini untuk tracking status peminjaman</li>
                    <li>• Anda akan menerima email notifikasi saat status berubah</li>
                    <li>• Jika ada pertanyaan, hubungi administrasi Lab Terpadu FEB UNDIP</li>
                </ul>
            </div>
        @endif
    </div>
</body>
</html>
