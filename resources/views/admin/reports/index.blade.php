@extends('layouts.admin')

@section('title', 'Laporan - Laboratorium dan Fasilitas Digital FEB UNDIP')

@section('content')
    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-indigo-600 rounded-2xl p-4 md:p-6 shadow-none">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Laporan</h1>
                    <p class="text-xs md:text-sm text-indigo-100">Export data Lab, BPS, dan Refinitiv</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-2 md:p-3 rounded-xl">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Type Tabs -->
    <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
        <div class="flex border-b border-gray-200">
            <button type="button" data-report-type="lab" 
               class="report-tab flex-1 px-4 py-4 text-center font-semibold transition-all {{ $reportType === 'lab' ? 'text-yellow-600 border-b-2 border-yellow-500 bg-yellow-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="hidden sm:inline">Peminjaman Lab</span>
                    <span class="sm:hidden">Lab</span>
                </div>
            </button>
            <button type="button" data-report-type="bps" 
               class="report-tab flex-1 px-4 py-4 text-center font-semibold transition-all {{ $reportType === 'bps' ? 'text-teal-600 border-b-2 border-teal-500 bg-teal-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="hidden sm:inline">Data BPS</span>
                    <span class="sm:hidden">BPS</span>
                </div>
            </button>
            <button type="button" data-report-type="refinitiv" 
               class="report-tab flex-1 px-4 py-4 text-center font-semibold transition-all {{ $reportType === 'refinitiv' ? 'text-blue-600 border-b-2 border-blue-500 bg-blue-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                    <span class="hidden sm:inline">Data Refinitiv</span>
                    <span class="sm:hidden">Refinitiv</span>
                </div>
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h2 class="font-bold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter Laporan <span id="filter-title"> {{ $reportTypes[$reportType] ?? '' }}</span>
            </h2>
        </div>
        
        <form id="filter-form" class="p-4 md:p-6">
            <input type="hidden" name="report_type" id="report_type" value="{{ $reportType }}">
            
            <div id="filter-fields" class="grid grid-cols-1 md:grid-cols-2 {{ $reportType === 'lab' ? 'lg:grid-cols-4' : 'lg:grid-cols-2' }} gap-4 mb-4">
                <!-- Start Month -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan Mulai</label>
                    <input type="month" name="start_month" id="start_month" value="{{ request('start_month') }}"
                        min="2000-01" max="2099-12"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- End Month -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan Akhir</label>
                    <input type="month" name="end_month" id="end_month" value="{{ request('end_month') }}"
                        min="2000-01" max="2099-12"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Lab Filter (only for lab report) -->
                <div id="lab-filter-container" class="{{ $reportType !== 'lab' ? 'hidden' : '' }}">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Laboratorium</label>
                    <select name="lab_id" id="lab_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Lab</option>
                        @foreach($labs as $lab)
                            <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Filter (only for lab report) -->
                <div id="type-filter-container" class="{{ $reportType !== 'lab' ? 'hidden' : '' }}">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Peminjaman</label>
                    <select name="type" id="type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Tipe</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-100">
                <button type="submit" id="btn-filter" class="flex-1 sm:flex-none px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span id="btn-filter-text">Tampilkan</span>
                </button>
                <button type="button" id="btn-reset" class="flex-1 sm:flex-none px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-all flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </button>
                <a href="#" id="btn-export-csv" class="flex-1 sm:flex-none px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
                <a href="#" id="btn-export-word" class="flex-1 sm:flex-none px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Word
                </a>
            </div>
        </form>
    </div>

    <!-- Data Container (will be updated via AJAX) -->
    <div id="data-container">
        @include('admin.reports.partials.table', ['data' => $data, 'reportType' => $reportType, 'reportTypes' => $reportTypes])
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="hidden fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 shadow-2xl flex items-center space-x-4">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 font-medium">Memuat data...</span>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const reportTypeInput = document.getElementById('report_type');
    const startMonthInput = document.getElementById('start_month');
    const endMonthInput = document.getElementById('end_month');
    const labIdInput = document.getElementById('lab_id');
    const typeInput = document.getElementById('type');
    const dataContainer = document.getElementById('data-container');
    const loadingOverlay = document.getElementById('loading-overlay');
    const btnFilterText = document.getElementById('btn-filter-text');
    const btnExportCsv = document.getElementById('btn-export-csv');
    const btnExportWord = document.getElementById('btn-export-word');
    const btnReset = document.getElementById('btn-reset');
    const filterTitle = document.getElementById('filter-title');
    const labFilterContainer = document.getElementById('lab-filter-container');
    const typeFilterContainer = document.getElementById('type-filter-container');
    const filterFields = document.getElementById('filter-fields');
    
    const reportTabs = document.querySelectorAll('.report-tab');
    
    const reportTypeNames = {
        'lab': 'Peminjaman Lab',
        'bps': 'Permohonan Data BPS',
        'refinitiv': 'Permohonan Data Refinitiv'
    };
    
    const tabColors = {
        'lab': { active: 'text-yellow-600 border-b-2 border-yellow-500 bg-yellow-50', inactive: 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' },
        'bps': { active: 'text-teal-600 border-b-2 border-teal-500 bg-teal-50', inactive: 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' },
        'refinitiv': { active: 'text-blue-600 border-b-2 border-blue-500 bg-blue-50', inactive: 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }
    };

    // Get current filter params
    function getFilterParams() {
        const params = new URLSearchParams();
        params.set('report_type', reportTypeInput.value);
        
        if (startMonthInput.value) params.set('start_month', startMonthInput.value);
        if (endMonthInput.value) params.set('end_month', endMonthInput.value);
        
        // Only include lab filters for lab report type
        if (reportTypeInput.value === 'lab') {
            if (labIdInput.value) params.set('lab_id', labIdInput.value);
            if (typeInput.value) params.set('type', typeInput.value);
        }
        
        return params;
    }

    // Update export URLs
    function updateExportUrls() {
        const params = getFilterParams();
        btnExportCsv.href = '{{ route("admin.reports.export") }}?' + params.toString();
        btnExportWord.href = '{{ route("admin.reports.export-word") }}?' + params.toString();
    }

    // Update tab styles
    function updateTabStyles(activeType) {
        reportTabs.forEach(tab => {
            const tabType = tab.dataset.reportType;
            const colors = tabColors[tabType];
            
            // Remove all possible classes
            tab.classList.remove(...colors.active.split(' '), ...colors.inactive.split(' '));
            
            // Add appropriate classes
            if (tabType === activeType) {
                tab.classList.add(...colors.active.split(' '));
            } else {
                tab.classList.add(...colors.inactive.split(' '));
            }
        });
    }

    // Update filter visibility based on report type
    function updateFilterVisibility(reportType) {
        if (reportType === 'lab') {
            labFilterContainer.classList.remove('hidden');
            typeFilterContainer.classList.remove('hidden');
            filterFields.classList.remove('lg:grid-cols-2');
            filterFields.classList.add('lg:grid-cols-4');
        } else {
            labFilterContainer.classList.add('hidden');
            typeFilterContainer.classList.add('hidden');
            filterFields.classList.remove('lg:grid-cols-4');
            filterFields.classList.add('lg:grid-cols-2');
            // Reset lab filters when switching away from lab
            labIdInput.value = '';
            typeInput.value = '';
        }
        
        // Update filter title
        filterTitle.textContent = reportTypeNames[reportType] || '';
    }

    // Fetch data via AJAX
    function fetchData(page = null) {
        if (!filterForm.checkValidity()) {
            // Don't report validity here as it steals focus during typing
            return;
        }

        loadingOverlay.classList.remove('hidden');
        btnFilterText.textContent = 'Memuat...';
        
        const params = getFilterParams();
        if (page) params.set('page', page);
        
        fetch('{{ route("admin.reports.index") }}?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            dataContainer.innerHTML = data.html;
            
            // Update browser URL without reload
            const newUrl = '{{ route("admin.reports.index") }}?' + params.toString();
            window.history.pushState({}, '', newUrl);
            
            // Re-attach pagination click handlers
            attachPaginationHandlers();
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        })
        .finally(() => {
            loadingOverlay.classList.add('hidden');
            btnFilterText.textContent = 'Tampilkan';
            updateExportUrls();
        });
    }

    // Attach click handlers to pagination links
    function attachPaginationHandlers() {
        const paginationLinks = dataContainer.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page');
                fetchData(page);
            });
        });
    }

    // Handle form submit
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            this.reportValidity();
            return;
        }
        fetchData();
    });

    // Handle reset button
    btnReset.addEventListener('click', function() {
        startMonthInput.value = '';
        endMonthInput.value = '';
        labIdInput.value = '';
        typeInput.value = '';
        fetchData();
    });

    // Handle tab click
    reportTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const newReportType = this.dataset.reportType;
            reportTypeInput.value = newReportType;
            
            // Update tab styles
            updateTabStyles(newReportType);
            
            // Update filter visibility
            updateFilterVisibility(newReportType);
            
            // Fetch data for new report type
            fetchData();
        });
    });

    // Auto-fetch when filter inputs change
    startMonthInput.addEventListener('change', function() {
        fetchData();
    });
    
    endMonthInput.addEventListener('change', function() {
        fetchData();
    });
    
    labIdInput.addEventListener('change', function() {
        fetchData();
    });
    
    typeInput.addEventListener('change', function() {
        fetchData();
    });

    // Initial setup
    updateExportUrls();
    attachPaginationHandlers();
});
</script>
@endpush
