@extends('layouts.admin')

@section('title', 'Kelola Pengumuman - Lab Digital FEB UNDIP')

@section('content')
    <!-- Header -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-1">Kelola Pengumuman</h1>
                    <p class="text-amber-100">Buat dan kelola pengumuman yang ditampilkan di landing page</p>
                </div>
                <button onclick="openModal()" class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Pengumuman
                </button>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Announcements List -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        @if($announcements->isEmpty())
            <div class="text-center py-16 px-4">
                <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <p class="text-gray-500 font-medium text-lg">Belum ada pengumuman</p>
                <p class="text-gray-400 text-sm mt-1">Klik "Tambah Pengumuman" untuk membuat pengumuman baru</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($announcements as $announcement)
                    @php
                        $typeConfig = [
                            'penting' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-400', 'label' => 'Penting'],
                            'info' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'border' => 'border-blue-400', 'label' => 'Info'],
                            'peringatan' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-400', 'label' => 'Peringatan'],
                        ];
                        $config = $typeConfig[$announcement->type] ?? $typeConfig['info'];
                    @endphp
                    <div class="p-5 hover:bg-gray-50 transition-colors border-l-4 {{ $config['border'] }} {{ !$announcement->is_active || $announcement->isExpired() ? 'opacity-50' : '' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-bold rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                                        {{ $config['label'] }}
                                    </span>
                                    @if(!$announcement->is_active)
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Nonaktif</span>
                                    @elseif($announcement->isExpired())
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Kedaluwarsa</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                                    @endif
                                </div>
                                <h3 class="font-bold text-gray-900 text-lg">{{ $announcement->title }}</h3>
                                <p class="text-gray-600 text-sm mt-1 line-clamp-2">{{ $announcement->content }}</p>
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                    <span>Oleh: {{ $announcement->creator->name ?? '-' }}</span>
                                    <span>{{ $announcement->created_at->format('d M Y, H:i') }}</span>
                                    @if($announcement->expires_at)
                                        <span class="text-amber-500">Kedaluwarsa: {{ $announcement->expires_at->format('d M Y, H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <!-- Toggle Active -->
                                <form action="{{ route('admin.announcements.toggle-active', $announcement) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="{{ $announcement->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        class="p-2 rounded-lg transition-colors {{ $announcement->is_active ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 hover:bg-gray-100' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($announcement->is_active)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                            @endif
                                        </svg>
                                    </button>
                                </form>
                                <!-- Edit -->
                                <button onclick="openEditModal(this)"
                                    data-id="{{ $announcement->id }}"
                                    data-title="{{ e($announcement->title) }}"
                                    data-content="{{ e($announcement->content) }}"
                                    data-type="{{ $announcement->type }}"
                                    data-expires="{{ $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '' }}"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <!-- Delete -->
                                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <div id="announcementModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="bg-amber-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Tambah Pengumuman</h3>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="announcementForm" method="POST" class="p-6 space-y-4">
                @csrf
                <div id="methodField"></div>

                <!-- Judul -->
                <div>
                    <label for="ann_title" class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="ann_title" name="title" required maxlength="255"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                        placeholder="Contoh: Lab EL 307 tidak tersedia">
                </div>

                <!-- Isi -->
                <div>
                    <label for="ann_content" class="block text-sm font-semibold text-gray-700 mb-2">
                        Isi Pengumuman <span class="text-red-500">*</span>
                    </label>
                    <textarea id="ann_content" name="content" rows="4" required maxlength="2000"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition resize-none"
                        placeholder="Jelaskan detail pengumuman..."></textarea>
                </div>

                <!-- Tipe -->
                <div>
                    <label for="ann_type" class="block text-sm font-semibold text-gray-700 mb-2">
                        Tipe <span class="text-red-500">*</span>
                    </label>
                    <select id="ann_type" name="type" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                        <option value="info">🔵 Info</option>
                        <option value="peringatan">🟡 Peringatan</option>
                        <option value="penting">🔴 Penting</option>
                    </select>
                </div>

                <!-- Tanggal Kedaluwarsa -->
                <div>
                    <label for="ann_expires" class="block text-sm font-semibold text-gray-700 mb-2">
                        Kedaluwarsa <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="datetime-local" id="ann_expires" name="expires_at"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    <p class="text-xs text-gray-400 mt-1">Kosongkan jika pengumuman tidak memiliki batas waktu</p>
                </div>

                <!-- Submit -->
                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span id="submitBtnText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Pengumuman';
        document.getElementById('submitBtnText').textContent = 'Simpan';
        document.getElementById('announcementForm').action = '{{ route("admin.announcements.store") }}';
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('ann_title').value = '';
        document.getElementById('ann_content').value = '';
        document.getElementById('ann_type').value = 'info';
        document.getElementById('ann_expires').value = '';
        document.getElementById('announcementModal').classList.remove('hidden');
        document.getElementById('announcementModal').classList.add('flex');
    }

    function openEditModal(btn) {
        var id = btn.getAttribute('data-id');
        var title = btn.getAttribute('data-title');
        var content = btn.getAttribute('data-content');
        var type = btn.getAttribute('data-type');
        var expiresAt = btn.getAttribute('data-expires');

        document.getElementById('modalTitle').textContent = 'Edit Pengumuman';
        document.getElementById('submitBtnText').textContent = 'Perbarui';
        document.getElementById('announcementForm').action = '/admin/announcements/' + id;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        document.getElementById('ann_title').value = title;
        document.getElementById('ann_content').value = content;
        document.getElementById('ann_type').value = type;
        document.getElementById('ann_expires').value = expiresAt || '';
        document.getElementById('announcementModal').classList.remove('hidden');
        document.getElementById('announcementModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('announcementModal').classList.add('hidden');
        document.getElementById('announcementModal').classList.remove('flex');
    }

    // Close modal on backdrop click
    document.getElementById('announcementModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
@endpush
