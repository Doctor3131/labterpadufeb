<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman - Lab Terpadu FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-10 md:h-16 w-auto object-contain">
                </div>
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-yellow-600">← Kembali ke Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8 max-w-4xl">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Detail Peminjaman</h1>
                    <div class="flex items-center space-x-3">
                        @if($booking->status === 'pending')
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded">⏳ Menunggu Persetujuan</span>
                        @elseif($booking->status === 'approved')
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded">✓ Disetujui</span>
                        @else
                            <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded">✗ Ditolak</span>
                        @endif
                        <span class="text-sm text-gray-500">ID: #{{ $booking->id }}</span>
                    </div>
                </div>

                @if($booking->status === 'pending')
                    <div class="flex space-x-2">
                        <form action="{{ route('admin.booking.approve', $booking->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" onclick="return confirm('Setujui peminjaman ini?')" class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg">
                                Setujui
                            </button>
                        </form>
                        <button onclick="showRejectModal()" class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg">
                            Tolak
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Informasi Laboratorium & Waktu -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Laboratorium & Waktu</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Laboratorium</label>
                    <p class="text-lg font-semibold text-yellow-600">{{ $booking->lab->name }}</p>
                    <p class="text-sm text-gray-500">Kapasitas: {{ $booking->lab->capacity }} orang</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tanggal</label>
                    <p class="text-lg">{{ \Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Waktu</label>
                    <p class="text-lg">{{ $booking->start_time }} - {{ $booking->end_time }} WIB</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Jumlah Peserta</label>
                    <p class="text-lg">{{ $booking->participant_count }} orang</p>
                </div>
            </div>
        </div>

        <!-- Informasi Peminjam -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Peminjam</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600">Nama Lengkap</label>
                    <p class="text-gray-800">{{ $booking->pic_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600">Program Studi</label>
                    <p class="text-gray-800">{{ $booking->study_program }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600">NIM</label>
                    <p class="text-gray-800">{{ $booking->nim }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600">No. Telepon</label>
                    <p class="text-gray-800">{{ $booking->phone_number }}</p>
                </div>
                @if($booking->address)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-600">Alamat</label>
                        <p class="text-gray-800">{{ $booking->address }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Detail Kegiatan -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Detail Kegiatan</h2>
            
            @if($booking->booking_type === 'non_perkuliahan')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Tipe Peminjaman</label>
                        <p class="text-gray-800">Non-Perkuliahan</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Nama Kegiatan</label>
                        <p class="text-lg font-semibold text-gray-800">{{ $booking->activity_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Jenis Kegiatan</label>
                        <p class="text-gray-800">{{ $booking->activity_type }}</p>
                    </div>
                    @if($booking->position)
                        <div>
                            <label class="block text-sm font-semibold text-gray-600">Jabatan</label>
                            <p class="text-gray-800">{{ $booking->position }}</p>
                        </div>
                    @endif
                    @if($booking->equipment_needs)
                        <div>
                            <label class="block text-sm font-semibold text-gray-600">Kebutuhan Peralatan</label>
                            <p class="text-gray-800">{{ $booking->equipment_needs }}</p>
                        </div>
                    @endif
                </div>
            @elseif($booking->booking_type === 'pribadi')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Tipe Peminjaman</label>
                        <p class="text-gray-800">Pribadi</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Status Peminjam</label>
                        <p class="text-gray-800">{{ $booking->applicant_status }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Angkatan</label>
                        <p class="text-gray-800">{{ $booking->class_year }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Keperluan</label>
                        <p class="text-gray-800">{{ $booking->purpose }}</p>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Tipe Peminjaman</label>
                        <p class="text-gray-800">{{ $booking->booking_type === 'perkuliahan_tetap' ? 'Perkuliahan Tetap' : 'Perkuliahan Tidak Tetap' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Mata Kuliah</label>
                        <p class="text-lg font-semibold text-gray-800">{{ $booking->course_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">Dosen Pengampu</label>
                        <p class="text-gray-800">{{ $booking->lecturer_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">NIP Dosen</label>
                        <p class="text-gray-800">{{ $booking->lecturer_nip }}</p>
                    </div>
                    @if($booking->software_needs)
                        <div>
                            <label class="block text-sm font-semibold text-gray-600">Software yang Digunakan</label>
                            <p class="text-gray-800">{{ $booking->software_needs }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Dokumen -->
        @if($booking->document_path)
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Dokumen Pendukung</h2>
                <a href="{{ asset('storage/' . $booking->document_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Dokumen
                </a>
            </div>
        @endif

        <!-- Rejection Reason -->
        @if($booking->status === 'rejected' && $booking->rejection_reason)
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <h2 class="text-xl font-bold text-red-800 mb-2">Alasan Penolakan</h2>
                <p class="text-red-700">{{ $booking->rejection_reason }}</p>
            </div>
        @endif

        <!-- Metadata -->
        <div class="bg-gray-100 rounded-lg p-4 text-sm text-gray-600">
            <p>Diajukan: {{ $booking->created_at->format('d M Y H:i') }} WIB</p>
            <p>Terakhir diupdate: {{ $booking->updated_at->format('d M Y H:i') }} WIB</p>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Tolak Peminjaman</h3>
                        <p class="text-sm text-gray-500">Berikan alasan penolakan</p>
                    </div>
                </div>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.booking.reject', $booking->id) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-3">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="rejection_reason" 
                        rows="4" 
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all" 
                        required 
                        placeholder="Contoh: Jadwal bertabrakan dengan kegiatan lain, dokumen tidak lengkap, dll..."
                    ></textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Peminjam akan melihat alasan ini
                    </p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold transition-all flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Tolak Peminjaman</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
            document.getElementById('rejectModal').classList.add('flex');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectModal').classList.remove('flex');
        }

        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>
