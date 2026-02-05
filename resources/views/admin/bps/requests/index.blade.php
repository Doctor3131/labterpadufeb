@extends('layouts.admin')

@section('title', 'Permintaan Data BPS - Lab Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-teal-600 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-blue-500 rounded-2xl p-4 md:p-6 shadow-none">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Permintaan Data BPS</h1>
                    <p class="text-xs md:text-sm text-blue-50">Kelola permintaan akses data BPS</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-2 md:p-3 rounded-xl">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
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

    <!-- Tabs -->
    <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
        <div class="border-b-2 border-gray-100 overflow-x-auto">
            <nav class="flex px-2 min-w-max" aria-label="Tabs">
                <button onclick="showBpsTab('pending')" class="bps-tab-button flex-1 flex flex-col items-center px-3 py-3 text-xs md:text-sm font-semibold border-b-3 border-blue-500 text-blue-700" data-tab="pending">
                    <div class="flex items-center justify-center">
                        <div class="bg-blue-100 p-2 rounded-lg mb-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="hidden md:inline">Menunggu</span>
                    <span class="mt-1 px-2 py-0.5 bg-blue-500 text-white rounded-full text-xs font-bold">{{ $pendingRequests->total() }}</span>
                </button>
                <button onclick="showBpsTab('completed')" class="bps-tab-button flex-1 flex flex-col items-center px-3 py-3 text-xs md:text-sm font-semibold border-b-3 border-transparent text-gray-500" data-tab="completed">
                    <div class="flex items-center justify-center">
                        <div class="bg-gray-100 p-2 rounded-lg mb-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="hidden md:inline">Selesai</span>
                    <span class="mt-1 px-2 py-0.5 bg-gray-300 text-gray-700 rounded-full text-xs font-bold">{{ $completedRequests->total() }}</span>
                </button>
            </nav>
        </div>

        <!-- Pending Tab -->
        <div id="bps-pending-tab" class="bps-tab-content p-3 md:p-6">
            @forelse($pendingRequests as $request)
                <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-lg hover:shadow-xl mb-3 p-4 border-l-4 border-blue-500 transition-all">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 bg-{{ $request->applicant_type === 'mahasiswa' ? 'purple' : 'indigo' }}-100 text-{{ $request->applicant_type === 'mahasiswa' ? 'purple' : 'indigo' }}-800 text-xs font-semibold rounded-lg">
                                {{ ucfirst($request->applicant_type) }}
                            </span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-lg">
                                {{ $request->display_purpose }}
                            </span>
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                                {{ $request->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <h3 class="text-base md:text-lg font-bold text-gray-800 mb-2">{{ $request->name }}</h3>
                    
                    <div class="space-y-1 text-sm text-gray-600 mb-4">
                        <p><strong>{{ $request->applicant_type === 'mahasiswa' ? 'NIM' : 'NIP' }}:</strong> {{ $request->identifier }}</p>
                        <p><strong>Email:</strong> {{ $request->email }}</p>
                        <p><strong>WhatsApp:</strong> {{ $request->phone }}</p>
                        @if($request->study_program)
                            <p><strong>Prodi:</strong> {{ $request->study_program }}</p>
                        @endif
                        <p><strong>Dataset:</strong> {{ $request->subData->count() }} dataset dipilih</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.bps.requests.show', $request) }}" class="flex-1 md:flex-none px-4 py-2 bg-blue-500 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 transition-colors text-center">
                            Detail
                        </a>
                        <form action="{{ route('admin.bps.requests.complete', $request) }}" method="POST" class="flex-1 md:flex-none" onsubmit="return confirm('Tandai permintaan ini sebagai selesai (data sudah dikirim)?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white text-sm font-semibold rounded-lg hover:bg-green-600 transition-colors">
                                ✓ Selesai
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="bg-blue-100 rounded-full p-4 inline-flex mb-4">
                        <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700 mb-2">Tidak Ada Permintaan Menunggu</h3>
                    <p class="text-gray-500">Semua permintaan data sudah diproses</p>
                </div>
            @endforelse

            @if($pendingRequests->hasPages())
                <div class="mt-6">
                    {{ $pendingRequests->links() }}
                </div>
            @endif
        </div>

        <!-- Completed Tab -->
        <div id="bps-completed-tab" class="bps-tab-content hidden p-3 md:p-6">
            @forelse($completedRequests as $request)
                <div class="bg-gradient-to-br from-white to-green-50 rounded-2xl shadow-lg hover:shadow-xl mb-3 p-4 border-l-4 border-green-500 transition-all">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-lg">
                                ✓ Selesai
                            </span>
                            <span class="px-3 py-1 bg-{{ $request->applicant_type === 'mahasiswa' ? 'purple' : 'indigo' }}-100 text-{{ $request->applicant_type === 'mahasiswa' ? 'purple' : 'indigo' }}-800 text-xs font-semibold rounded-lg">
                                {{ ucfirst($request->applicant_type) }}
                            </span>
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                                {{ $request->completed_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>

                    <h3 class="text-base md:text-lg font-bold text-gray-800 mb-2">{{ $request->name }}</h3>
                    
                    <div class="space-y-1 text-sm text-gray-600 mb-4">
                        <p><strong>{{ $request->applicant_type === 'mahasiswa' ? 'NIM' : 'NIP' }}:</strong> {{ $request->identifier }}</p>
                        <p><strong>Email:</strong> {{ $request->email }}</p>
                        <p><strong>Keperluan:</strong> {{ $request->display_purpose }}</p>
                    </div>

                    <a href="{{ route('admin.bps.requests.show', $request) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                        Lihat Detail
                    </a>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="bg-gray-100 rounded-full p-4 inline-flex mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700 mb-2">Belum Ada Data Selesai</h3>
                    <p class="text-gray-500">Permintaan yang sudah selesai akan muncul di sini</p>
                </div>
            @endforelse

            @if($completedRequests->hasPages())
                <div class="mt-6">
                    {{ $completedRequests->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showBpsTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.bps-tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.bps-tab-button').forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-700');
            btn.classList.add('border-transparent', 'text-gray-500');
            btn.querySelector('div > div').classList.remove('bg-blue-100');
            btn.querySelector('div > div').classList.add('bg-gray-100');
        });

        // Show selected tab
        document.getElementById('bps-' + tabName + '-tab').classList.remove('hidden');
        const activeBtn = document.querySelector(`.bps-tab-button[data-tab="${tabName}"]`);
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
        activeBtn.classList.add('border-blue-500', 'text-blue-700');
        activeBtn.querySelector('div > div').classList.remove('bg-gray-100');
        activeBtn.querySelector('div > div').classList.add('bg-blue-100');
    }
</script>
@endpush
