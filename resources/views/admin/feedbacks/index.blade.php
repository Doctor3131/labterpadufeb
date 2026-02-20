@extends('layouts.admin')

@section('title', 'Kelola Feedback - Laboratorium dan Fasilitas Digital FEB UNDIP')

@section('content')
    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-purple-500 rounded-2xl p-4 md:p-6 shadow-none">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Kelola Feedback</h1>
                    <p class="text-xs md:text-sm text-purple-50">Tinjau dan kelola laporan masalah dari pengguna</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-2 md:p-3 rounded-xl">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-4 md:px-6 py-4 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium text-sm md:text-base">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Feedback List -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        @forelse($feedbacks as $feedback)
            <div class="p-4 md:p-6 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <!-- Left: Feedback Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="text-lg font-bold text-gray-900">{{ $feedback->title }}</h3>
                            @if($feedback->status === 'pending')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Pending</span>
                            @elseif($feedback->status === 'reviewed')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Reviewed</span>
                            @else
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Resolved</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-3">{{ $feedback->detail }}</p>
                        <p class="text-xs text-gray-400">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            {{ $feedback->created_at->diffForHumans() }}
                        </p>
                        
                        @if($feedback->admin_notes)
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs font-semibold text-blue-600 mb-1">Catatan Admin:</p>
                                <p class="text-sm text-blue-700">{{ $feedback->admin_notes }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex md:flex-col gap-2">
                        <button onclick="showUpdateModal({{ $feedback->id }}, '{{ $feedback->status }}', '{{ addslashes($feedback->admin_notes ?? '') }}')" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Update
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="inline-block p-5 bg-gray-100 rounded-2xl mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Tidak Ada Feedback</h3>
                <p class="text-sm text-gray-600">Belum ada laporan masalah dari pengguna</p>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($feedbacks->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $feedbacks->links() }}
            </div>
        @endif
    </div>

    <!-- Update Modal -->
    <div id="updateModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden items-center justify-center z-50 px-4">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Update Feedback</h3>
                        <p class="text-sm text-gray-500">Ubah status dan catatan</p>
                    </div>
                </div>
                <button onclick="closeUpdateModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="updateForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" id="updateStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan Admin</label>
                    <textarea name="admin_notes" id="updateNotes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Tambahkan catatan (opsional)"></textarea>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeUpdateModal()" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showUpdateModal(id, status, notes) {
        const modal = document.getElementById('updateModal');
        const form = document.getElementById('updateForm');
        
        form.action = `/admin/feedbacks/${id}`;
        document.getElementById('updateStatus').value = status;
        document.getElementById('updateNotes').value = notes;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeUpdateModal() {
        const modal = document.getElementById('updateModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal when clicking outside
    document.getElementById('updateModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeUpdateModal();
        }
    });

    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeUpdateModal();
        }
    });
</script>
@endpush
