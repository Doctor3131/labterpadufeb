<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Berhasil - Lab Terpadu FEB UNDIP</title>
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
                <a href="{{ route('login') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                    Login Asisten Lab
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-12 max-w-3xl">
        <!-- Success Card -->
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <!-- Success Icon -->
            <div class="mb-6">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-3">Peminjaman Berhasil Diajukan!</h1>
            <p class="text-gray-600 mb-8">Permintaan peminjaman Anda telah diterima dan menunggu persetujuan dari Asisten Lab</p>

            <!-- Booking Details -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Detail Peminjaman</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">ID Booking:</span>
                        <span class="font-semibold text-gray-800">#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Tipe:</span>
                        <span class="font-semibold text-gray-800">
                            @if($booking->booking_type === 'perkuliahan_tetap')
                                Perkuliahan Tetap
                            @elseif($booking->booking_type === 'perkuliahan_tidak_tetap')
                                Perkuliahan Tidak Tetap
                            @elseif($booking->booking_type === 'pribadi')
                                Pribadi
                            @else
                                Non-Perkuliahan
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Nama Peminjam:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->pic_name }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Laboratorium:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->lab->name }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Tanggal:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->booking_date->format('d F Y') }} ({{ $booking->day }})</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Waktu:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->start_time }} - {{ $booking->end_time }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Jumlah Peserta:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->participant_count }} orang</span>
                    </div>

                    @if($booking->isPerkuliahan())
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Mata Kuliah:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->course_name }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Dosen:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->lecturer_name }}</span>
                        </div>
                    @endif

                    @if($booking->isNonPerkuliahan())
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Jenis Kegiatan:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->activity_type }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Nama Kegiatan:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->activity_name }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                            ⏱ Menunggu Persetujuan
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tracking Link Box -->
            <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-300 rounded-xl p-6 mb-6 text-left">
                <div class="flex items-start mb-4">
                    <div class="flex-shrink-0 bg-red-500 rounded-full p-2 mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-red-900 text-lg mb-2">⚠️ PERINGATAN PENTING!</h4>
                        <p class="text-red-800 font-semibold">
                            Wajib simpan salah satu dari opsi berikut untuk melacak status peminjaman Anda!
                        </p>
                    </div>
                </div>

                <!-- Option 1: Full Link -->
                <div class="bg-white border-2 border-gray-300 rounded-lg p-4 mb-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-3">
                        <div class="bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">1</div>
                        <h5 class="font-bold text-gray-800 text-lg">Opsi 1: Simpan Link Tracking Lengkap</h5>
                    </div>
                    <p class="text-sm text-gray-700 mb-3">
                        <strong>Rekomendasi:</strong> Link lengkap lebih mudah - tinggal klik untuk cek status!
                    </p>
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-300 mb-3">
                        <input type="text" 
                               value="{{ route('booking.track', $booking->tracking_token) }}" 
                               id="trackingLink" 
                               readonly 
                               class="w-full text-sm text-gray-700 bg-transparent border-none focus:outline-none select-all">
                    </div>
                    <div class="flex gap-2">
                        <button onclick="copyTrackingLink()" 
                                id="copyLinkBtn"
                                class="flex-1 bg-gray-700 hover:bg-gray-800 text-white px-4 py-3 rounded-lg font-bold transition-all flex items-center justify-center shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Salin Link Lengkap
                        </button>
                        <a href="{{ route('booking.track', $booking->tracking_token) }}" 
                           target="_blank"
                           class="flex-1 bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-600 px-4 py-3 rounded-lg font-bold text-center transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Buka Tracking
                        </a>
                    </div>
                </div>

                <!-- Option 2: Token Only -->
                <div class="bg-white border-2 border-gray-300 rounded-lg p-4 mb-4 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-3">
                        <div class="bg-gray-700 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">2</div>
                        <h5 class="font-bold text-gray-800 text-lg">Opsi 2: Simpan Kode Token Saja</h5>
                    </div>
                    <p class="text-sm text-gray-700 mb-3">
                        Token lebih pendek & mudah dicatat. Nanti masukkan di halaman beranda untuk tracking.
                    </p>
                    <div class="bg-gray-100 rounded-lg p-4 border-2 border-gray-300 mb-3">
                        <p class="text-xs text-gray-600 font-semibold mb-2">KODE TRACKING ANDA:</p>
                        <input type="text" 
                               value="{{ $booking->tracking_token }}" 
                               id="trackingToken" 
                               readonly 
                               class="w-full text-center text-xl font-mono font-bold text-gray-900 bg-transparent border-none focus:outline-none select-all uppercase tracking-wider">
                    </div>
                    <button onclick="copyToken()" 
                            id="copyTokenBtn"
                            class="w-full bg-gray-700 hover:bg-gray-800 text-white px-4 py-3 rounded-lg font-bold transition-all flex items-center justify-center shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Salin Kode Token
                    </button>
                </div>

                <!-- Tips Box -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg p-4">
                    <p class="text-xs text-yellow-800 font-bold mb-2">
                        💡 TIPS MENYIMPAN:
                    </p>
                    <ul class="text-xs text-yellow-800 space-y-1">
                        <li>✅ Screenshot halaman ini dan simpan di galeri</li>
                        <li>✅ Kirim link/token ke WhatsApp/email pribadi Anda</li>
                        <li>✅ Catat di notes/memo HP Anda</li>
                        <li>✅ Bookmark halaman tracking di browser</li>
                    </ul>
                </div>
            </div>

            <!-- Information Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 text-left">
                <h4 class="font-bold text-blue-800 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi Penting
                </h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Permintaan Anda akan diproses oleh Asisten Lab</li>
                    <li>• Anda akan menerima notifikasi via email jika tersedia</li>
                    <li>• Gunakan link/token tracking untuk cek status kapan saja</li>
                    <li>• Status akan berubah: <span class="font-bold">Pending → Approved/Rejected</span></li>
                    <li>• Jika ada pertanyaan, hubungi administrasi Lab Terpadu FEB UNDIP</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing') }}" 
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all">
                    Kembali ke Beranda
                </a>
                <a href="{{ route('booking.create') }}" 
                    class="bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 px-8 py-3 rounded-lg font-semibold transition-all">
                    Ajukan Peminjaman Lagi
                </a>
            </div>
        </div>
    </div>

    <script>
        function copyTrackingLink() {
            const linkInput = document.getElementById('trackingLink');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999);
            
            navigator.clipboard.writeText(linkInput.value).then(function() {
                const btn = document.getElementById('copyLinkBtn');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Link Tersalin!
                `;
                btn.classList.remove('bg-gray-700', 'hover:bg-gray-800');
                btn.classList.add('bg-gray-900');
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('bg-gray-900');
                    btn.classList.add('bg-gray-700', 'hover:bg-gray-800');
                }, 2000);
            }, function(err) {
                document.execCommand('copy');
                alert('✅ Link tracking berhasil disalin!');
            });
        }

        function copyToken() {
            const tokenInput = document.getElementById('trackingToken');
            tokenInput.select();
            tokenInput.setSelectionRange(0, 99999);
            
            navigator.clipboard.writeText(tokenInput.value).then(function() {
                const btn = document.getElementById('copyTokenBtn');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Token Tersalin!
                `;
                btn.classList.remove('bg-gray-700', 'hover:bg-gray-800');
                btn.classList.add('bg-gray-900');
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('bg-gray-900');
                    btn.classList.add('bg-gray-700', 'hover:bg-gray-800');
                }, 2000);
            }, function(err) {
                document.execCommand('copy');
                alert('✅ Kode token berhasil disalin!');
            });
        }
    </script>
</body>
</html>
