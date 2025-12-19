<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - Lab Terpadu FEB UNDIP</title>
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
                    <span class="ml-4 text-sm bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full">Admin Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('landing') }}" class="text-gray-600 hover:text-yellow-600">Lihat Jadwal</a>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                            + Buat User Baru
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pending</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $pendingBookings->count() }}</p>
                    </div>
                    <div class="text-4xl">⏳</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Approved</p>
                        <p class="text-3xl font-bold text-green-600">{{ $approvedBookings->count() }}</p>
                    </div>
                    <div class="text-4xl">✅</div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Rejected</p>
                        <p class="text-3xl font-bold text-red-600">{{ $rejectedBookings->count() }}</p>
                    </div>
                    <div class="text-4xl">❌</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="showTab('pending')" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-yellow-500 text-yellow-600" data-tab="pending">
                        Menunggu Persetujuan ({{ $pendingBookings->count() }})
                    </button>
                    <button onclick="showTab('approved')" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="approved">
                        Disetujui ({{ $approvedBookings->count() }})
                    </button>
                    <button onclick="showTab('rejected')" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="rejected">
                        Ditolak ({{ $rejectedBookings->count() }})
                    </button>
                </nav>
            </div>
        </div>

        <!-- Pending Bookings -->
        <div id="pending-tab" class="tab-content">
            @forelse($pendingBookings as $booking)
                <div class="bg-white rounded-lg shadow mb-4 p-6 hover:shadow-lg transition-shadow">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded">{{ $booking->lab->name }}</span>
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded">
                                    {{ \Carbon\Carbon::parse($booking->tanggal)->locale('id')->isoFormat('D MMMM Y') }}
                                </span>
                                <span class="text-gray-600 text-sm">{{ $booking->start_time }} - {{ $booking->end_time }}</span>
                            </div>
                            
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">
                                @if($booking->booking_type === 'non_perkuliahan')
                                    {{ $booking->nama_kegiatan }}
                                @else
                                    {{ $booking->mata_kuliah }}
                                @endif
                            </h3>
                            
                            <div class="text-sm text-gray-600 space-y-1">
                                <p><strong>Peminjam:</strong> {{ $booking->nama_peminjam }} ({{ $booking->program_studi }})</p>
                                @if($booking->booking_type !== 'non_perkuliahan')
                                    <p><strong>Dosen:</strong> {{ $booking->dosen_pengampu }}</p>
                                @endif
                                <p><strong>Peserta:</strong> {{ $booking->jumlah_peserta }} orang</p>
                                <p><strong>Diajukan:</strong> {{ $booking->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <div class="flex space-x-2 ml-4">
                            <a href="{{ route('admin.booking.show', $booking->id) }}" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg transition-colors">
                                Detail
                            </a>
                            <button onclick="approveBooking({{ $booking->id }})" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm rounded-lg transition-colors">
                                Setujui
                            </button>
                            <button onclick="showRejectModal({{ $booking->id }})" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded-lg transition-colors">
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <div class="text-gray-300 text-6xl mb-4">📭</div>
                    <p class="text-gray-500 text-lg">Tidak ada peminjaman yang menunggu persetujuan</p>
                </div>
            @endforelse
        </div>

        <!-- Approved Bookings -->
        <div id="approved-tab" class="tab-content hidden">
            @forelse($approvedBookings as $booking)
                <div class="bg-white rounded-lg shadow mb-4 p-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded">✓ Disetujui</span>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded">{{ $booking->lab->name }}</span>
                        <span class="text-gray-600 text-sm">
                            {{ \Carbon\Carbon::parse($booking->tanggal)->locale('id')->isoFormat('D MMM Y') }} • {{ $booking->start_time }}
                        </span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        @if($booking->booking_type === 'non_perkuliahan')
                            {{ $booking->nama_kegiatan }}
                        @else
                            {{ $booking->mata_kuliah }}
                        @endif
                    </h3>
                    <p class="text-sm text-gray-600">{{ $booking->nama_peminjam }} • {{ $booking->jumlah_peserta }} orang</p>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500">Belum ada peminjaman yang disetujui</p>
                </div>
            @endforelse
        </div>

        <!-- Rejected Bookings -->
        <div id="rejected-tab" class="tab-content hidden">
            @forelse($rejectedBookings as $booking)
                <div class="bg-white rounded-lg shadow mb-4 p-6">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded">✗ Ditolak</span>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded">{{ $booking->lab->name }}</span>
                        <span class="text-gray-600 text-sm">
                            {{ \Carbon\Carbon::parse($booking->tanggal)->locale('id')->isoFormat('D MMM Y') }}
                        </span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        @if($booking->booking_type === 'non_perkuliahan')
                            {{ $booking->nama_kegiatan }}
                        @else
                            {{ $booking->mata_kuliah }}
                        @endif
                    </h3>
                    <p class="text-sm text-gray-600 mb-2">{{ $booking->nama_peminjam }}</p>
                    @if($booking->rejection_reason)
                        <div class="mt-2 p-3 bg-red-50 rounded text-sm text-red-700">
                            <strong>Alasan:</strong> {{ $booking->rejection_reason }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <p class="text-gray-500">Belum ada peminjaman yang ditolak</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Tolak Peminjaman</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Alasan Penolakan *</label>
                    <textarea name="rejection_reason" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent" required placeholder="Berikan alasan penolakan..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg">
                        Tolak Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active state from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-yellow-500', 'text-yellow-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            
            // Add active state to clicked button
            const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
            activeBtn.classList.add('border-yellow-500', 'text-yellow-600');
            activeBtn.classList.remove('border-transparent', 'text-gray-500');
        }

        // Approve booking
        function approveBooking(id) {
            if (confirm('Setujui peminjaman ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/bookings/${id}/approve`;
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Show reject modal
        function showRejectModal(id) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/admin/bookings/${id}/reject`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Close reject modal
        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>
