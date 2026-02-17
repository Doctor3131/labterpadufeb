<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Jadwal - Lab Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 border-b-4 border-yellow-500">
        <div class="container mx-auto px-3 sm:px-4 md:px-6 py-3 md:py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('landing') }}">
                        <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-12 sm:h-14 md:h-16 w-auto object-contain">
                    </a>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg font-medium text-sm md:text-base">
                        ← <span class="hidden sm:inline">Kembali ke </span>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-3 sm:px-4 md:px-6 py-4 md:py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 md:mb-6 gap-3 md:gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-800">Manajemen Jadwal</h1>
                <p class="text-sm md:text-base text-gray-600">Kelola semua jadwal laboratorium</p>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto">
                {{-- View Toggle --}}
                <div class="flex bg-gray-100 rounded-lg p-1 shadow-inner">
                    <button type="button" id="btn-view-list" class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span class="hidden sm:inline">List</span>
                    </button>
                    <button type="button" id="btn-view-timetable" class="flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        <span class="hidden sm:inline">Timetable</span>
                    </button>
                </div>
                <a href="{{ route('admin.schedules.create') }}" 
                   class="flex items-center justify-center px-4 py-3 md:py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all text-sm md:text-base flex-1 md:flex-none">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Jadwal
                </a>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- ==================== LIST VIEW ==================== --}}
        <div id="list-view-section">
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-md p-3 md:p-4 mb-4 md:mb-6">
                <div class="space-y-3 md:space-y-0 md:flex md:flex-wrap md:gap-4">
                    <div class="md:flex-1 md:min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <input type="month" id="filter-month" value="{{ request('month') }}" 
                               class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-base md:text-sm">
                    </div>
                    <div class="md:flex-1 md:min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" id="filter-date" value="{{ request('date') }}" 
                               class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-base md:text-sm">
                    </div>
                    <div class="md:flex-1 md:min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lab</label>
                        <select id="filter-lab" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-base md:text-sm">
                            <option value="">Semua Lab</option>
                            @foreach($labs as $lab)
                                <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>
                                    {{ $lab->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:flex-1 md:min-w-[120px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hari</label>
                        <select id="filter-day" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-base md:text-sm">
                            <option value="">Semua Hari</option>
                            <option value="Senin" {{ request('day') == 'Senin' ? 'selected' : '' }}>Senin</option>
                            <option value="Selasa" {{ request('day') == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                            <option value="Rabu" {{ request('day') == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                            <option value="Kamis" {{ request('day') == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                            <option value="Jumat" {{ request('day') == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                            <option value="Sabtu" {{ request('day') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                        </select>
                    </div>
                    <div class="md:flex-1 md:min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                        <select id="filter-type" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-base md:text-sm">
                            <option value="">Semua Tipe</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:flex-1 md:min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" id="filter-search" value="{{ request('search') }}" 
                               placeholder="Kegiatan, Matkul, atau Dosen..."
                               class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-base md:text-sm">
                    </div>
                    <div class="flex gap-2 md:items-end">
                        <button type="button" id="btn-reset" class="flex-1 md:flex-none px-4 py-2.5 md:py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg text-sm text-center">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Schedule Table/Cards -->
            <div id="schedule-container" class="bg-white rounded-xl shadow-md overflow-hidden">
                @include('admin.schedules.partials.table', ['schedules' => $schedules])
            </div>
        </div>

        {{-- ==================== TIMETABLE VIEW ==================== --}}
        <div id="timetable-view-section" class="hidden">
            {{-- Week Navigation --}}
            <div class="bg-white rounded-xl shadow-md mb-4 md:mb-6">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <button type="button" id="tt-prev-week" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div class="flex items-center gap-3">
                        <h3 id="tt-week-label" class="text-sm md:text-base font-semibold text-gray-800">Memuat...</h3>
                        <input type="date" id="tt-date-picker" max="9999-12-31" class="px-2 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500">
                        <button type="button" id="tt-today-btn" class="px-3 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg text-xs font-medium transition-colors">Hari Ini</button>
                    </div>
                    <button type="button" id="tt-next-week" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                {{-- Day Tabs --}}
                <div id="tt-day-tabs" class="flex overflow-x-auto"></div>
            </div>

            {{-- Legend --}}
            <div class="flex flex-wrap gap-3 mb-4 px-1">
                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                    <div class="w-3 h-3 rounded bg-yellow-300 border border-yellow-400"></div> Kuliah Tetap
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                    <div class="w-3 h-3 rounded bg-indigo-400 border border-indigo-500"></div> Kuliah Tidak Tetap
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                    <div class="w-3 h-3 rounded bg-emerald-400 border border-emerald-500"></div> Non Perkuliahan
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                    <div class="w-3 h-3 rounded bg-orange-400 border border-orange-500"></div> Pribadi
                </div>
            </div>

            {{-- Timetable Grid --}}
            <div id="tt-grid-container" class="bg-white rounded-xl shadow-md overflow-x-auto"></div>
        </div>
    </div>
    <script>
        // Custom Dropdown Implementation (Reused from form.blade.php)
        class CustomSelect {
            constructor(originalSelect) {
                this.originalSelect = originalSelect;
                this.originalSelect.style.display = 'none'; // Hide original
                
                // Create wrapper
                this.wrapper = document.createElement('div');
                this.wrapper.className = 'relative custom-select-wrapper w-full';
                this.originalSelect.parentNode.insertBefore(this.wrapper, this.originalSelect);
                this.wrapper.appendChild(this.originalSelect); // Move original inside
                
                // Create Trigger Element
                this.trigger = document.createElement('button');
                this.trigger.type = 'button';
                this.trigger.className = 'w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-base md:text-sm text-left bg-white flex justify-between items-center transition-shadow duration-200';
                
                // Content span
                this.triggerLabel = document.createElement('span');
                this.triggerLabel.className = 'block truncate text-gray-700';
                
                // Chevron icon
                const chevron = document.createElement('div');
                chevron.innerHTML = `<svg class="w-4 h-4 text-gray-400 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
                this.chevronIcon = chevron.firstElementChild;

                this.trigger.appendChild(this.triggerLabel);
                this.trigger.appendChild(chevron);
                this.wrapper.appendChild(this.trigger);

                // Create Options Container
                this.optionsContainer = document.createElement('div');
                this.optionsContainer.className = 'absolute z-50 w-full bg-white shadow-xl max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm mt-1 hidden scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 option-container-anim';
                this.wrapper.appendChild(this.optionsContainer);

                // Initialize
                this.initOptions();
                this.updateTrigger();

                // Event Listeners
                this.trigger.addEventListener('click', (e) => {
                    if (this.trigger.hasAttribute('disabled')) return;
                    e.stopPropagation();
                    this.toggleDropdown();
                });

                // Close when clicking outside
                document.addEventListener('click', (e) => {
                    if (!this.wrapper.contains(e.target)) {
                        this.closeDropdown();
                    }
                });

                // Listen for changes
                this.originalSelect.addEventListener('change', () => {
                   this.updateTrigger();
                   this.initOptions();
                });
            }

            initOptions() {
                this.optionsContainer.innerHTML = '';
                Array.from(this.originalSelect.options).forEach(option => {
                    // Skip if disabled (unless it's a placeholder we want to show, but for filters usually all optional)
                     if (option.disabled) return;

                    const optionDiv = document.createElement('div');
                    optionDiv.className = `text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-yellow-50 transition-colors duration-150 border-b border-gray-50 last:border-0`;
                    optionDiv.textContent = option.text;
                    
                    if (option.selected) {
                        optionDiv.classList.add('bg-blue-50', 'text-blue-900', 'font-medium');
                        const check = document.createElement('span');
                        check.className = 'absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600';
                        check.innerHTML = `<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>`;
                        optionDiv.appendChild(check);
                    }

                    optionDiv.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.originalSelect.value = option.value;
                        this.originalSelect.dispatchEvent(new Event('change'));
                        this.closeDropdown();
                    });

                    this.optionsContainer.appendChild(optionDiv);
                });
            }

            updateTrigger() {
                const selectedOption = this.originalSelect.options[this.originalSelect.selectedIndex];
                this.triggerLabel.textContent = selectedOption ? selectedOption.text : 'Pilih...';
            }

            toggleDropdown() {
                const isHidden = this.optionsContainer.classList.contains('hidden');
                // Close others
                document.querySelectorAll('.custom-select-wrapper .options-container').forEach(el => {
                    if (!el.classList.contains('hidden') && el !== this.optionsContainer) {
                        el.classList.add('hidden');
                         const otherChevron = el.parentElement.querySelector('svg');
                         if(otherChevron) otherChevron.classList.remove('rotate-180');
                    }
                });

                if (isHidden) {
                    this.optionsContainer.classList.remove('hidden');
                    this.chevronIcon.classList.add('rotate-180');
                } else {
                    this.closeDropdown();
                }
            }

            closeDropdown() {
                this.optionsContainer.classList.add('hidden');
                this.chevronIcon.classList.remove('rotate-180');
            }
        }

        // AJAX Filter Implementation
        let searchTimeout = null;
        const DEBOUNCE_DELAY = 400; // milliseconds
        const FILTER_STORAGE_KEY = 'admin_schedules_filters';

        // Save filters to localStorage
        function saveFiltersToLocalStorage() {
            try {
                const filters = {
                    month: document.getElementById('filter-month').value,
                    date: document.getElementById('filter-date').value,
                    lab: document.getElementById('filter-lab').value,
                    day: document.getElementById('filter-day').value,
                    type: document.getElementById('filter-type').value,
                    search: document.getElementById('filter-search').value
                };
                localStorage.setItem(FILTER_STORAGE_KEY, JSON.stringify(filters));
            } catch (e) {
                console.warn('Failed to save filters to localStorage:', e);
            }
        }

        // Load filters from localStorage (silent mode - no AJAX trigger)
        function loadFiltersFromLocalStorage() {
            const savedFilters = localStorage.getItem(FILTER_STORAGE_KEY);
            if (!savedFilters) return false;

            try {
                const filters = JSON.parse(savedFilters);
                
                // Set values silently (before event listeners are attached)
                document.getElementById('filter-month').value = filters.month || '';
                document.getElementById('filter-date').value = filters.date || '';
                document.getElementById('filter-lab').value = filters.lab || '';
                document.getElementById('filter-day').value = filters.day || '';
                document.getElementById('filter-type').value = filters.type || '';
                document.getElementById('filter-search').value = filters.search || '';
                
                return true; // Filters were loaded
            } catch (e) {
                console.error('Error loading filters from localStorage:', e);
                return false;
            }
        }

        // Clear filters from localStorage
        function clearFiltersFromLocalStorage() {
            localStorage.removeItem(FILTER_STORAGE_KEY);
        }

        function loadSchedules(url = null, append = false) {
            const container = document.getElementById('schedule-container');
            
            // If not append (filtering), show loading state
            if (!append) {
                container.innerHTML = `
                    <div class="p-8 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-500 mx-auto"></div>
                        <p class="text-gray-500 mt-2 text-sm">Memuat jadwal...</p>
                    </div>
                `;
            } else {
                // Show loading on button
                const btn = document.getElementById('btn-load-more');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = `<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-yellow-700 mr-2"></div> Memuat...`;
                }
            }

            // Build URL
            let fetchUrl;
            if (url) {
                fetchUrl = url;
            } else {
                // Save current filters to localStorage only when filtering (not loading more)
                saveFiltersToLocalStorage();
                
                const filterMonth = document.getElementById('filter-month').value;
                const filterDate = document.getElementById('filter-date').value;
                const filterLab = document.getElementById('filter-lab').value;
                const filterDay = document.getElementById('filter-day').value;
                const filterType = document.getElementById('filter-type').value;
                const filterSearch = document.getElementById('filter-search').value;

                // Build query string
                const params = new URLSearchParams();
                if (filterMonth) params.append('month', filterMonth);
                if (filterDate) params.append('date', filterDate);
                if (filterLab) params.append('lab_id', filterLab);
                if (filterDay) params.append('day', filterDay);
                if (filterType) params.append('type', filterType);
                if (filterSearch) params.append('search', filterSearch);
                
                fetchUrl = `{{ route('admin.schedules.index') }}?${params.toString()}`;
            }

            // Fetch with AJAX
            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                if (append) {
                    // APPEND MODE: Parse HTML and append rows/cards
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Append Desktop Rows
                    const newRows = doc.querySelectorAll('#desktop-table tbody tr');
                    const currentTbody = document.querySelector('#desktop-table tbody');
                    if (currentTbody && newRows.length) {
                        newRows.forEach(row => currentTbody.appendChild(row));
                    }

                    // Append Mobile Cards
                    const newCards = doc.querySelectorAll('#mobile-cards > div'); // direct children divs
                    const currentCards = document.getElementById('mobile-cards');
                    if (currentCards && newCards.length) {
                        newCards.forEach(card => currentCards.appendChild(card));
                    }

                    // Update Load More Button (replace container)
                    const newLoadMore = doc.getElementById('load-more-container');
                    const currentLoadMore = document.getElementById('load-more-container');
                    if (newLoadMore) {
                        if (currentLoadMore) {
                            currentLoadMore.replaceWith(newLoadMore);
                        } else {
                             // Insert after mobile cards (or schedule container end)
                             container.appendChild(newLoadMore); 
                        }
                    } else if (currentLoadMore) {
                        currentLoadMore.remove(); // No more pages
                    }

                    // Update Total Count
                    const newCount = doc.getElementById('schedule-count');
                    const currentCount = document.getElementById('schedule-count');
                    if (newCount && currentCount) {
                        currentCount.replaceWith(newCount);
                    }

                } else {
                    // REPLACE MODE: Just replace innerHTML
                    container.innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error loading schedules:', error);
                // Error handling...
                const errorHtml = `
                    <div class="p-8 text-center text-red-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Gagal memuat jadwal. Silakan coba lagi.
                    </div>
                `;
                
                if (!append) {
                    container.innerHTML = errorHtml;
                } else {
                    // Revert button state if append failed
                     const btn = document.getElementById('btn-load-more');
                     if(btn) {
                        btn.disabled = false;
                        btn.innerHTML = `<span>Coba Lagi</span>`;
                     }
                     alert('Gagal memuat halaman berikutnya.');
                }
            });
        }

        function resetFilters() {
            document.getElementById('filter-month').value = '';
            document.getElementById('filter-date').value = '';
            document.getElementById('filter-lab').value = '';
            document.getElementById('filter-day').value = '';
            document.getElementById('filter-type').value = '';
            document.getElementById('filter-search').value = '';
            
            // Trigger change events to update custom selects
            document.getElementById('filter-lab').dispatchEvent(new Event('change'));
            document.getElementById('filter-day').dispatchEvent(new Event('change'));
            document.getElementById('filter-type').dispatchEvent(new Event('change'));
            
            // Clear from localStorage
            clearFiltersFromLocalStorage();
            
            loadSchedules();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Load saved filters from localStorage
            const filtersLoaded = loadFiltersFromLocalStorage();

            // Initialize custom dropdowns
            const filterLab = document.getElementById('filter-lab');
            const filterDay = document.getElementById('filter-day');
            const filterType = document.getElementById('filter-type');
            
            if (filterLab) new CustomSelect(filterLab);
            if (filterDay) new CustomSelect(filterDay);
            if (filterType) new CustomSelect(filterType);

            // Add event listeners for auto-filter
            const triggerLoad = () => loadSchedules(); // Default: replace mode

            document.getElementById('filter-month').addEventListener('change', function() {
                // Mutual exclusion: Clear date if month is selected
                if (this.value) {
                    document.getElementById('filter-date').value = '';
                }
                triggerLoad();
            });

            document.getElementById('filter-date').addEventListener('change', function() {
                // Mutual exclusion: Clear month if date is selected
                if (this.value) {
                    document.getElementById('filter-month').value = '';
                }
                triggerLoad();
            });
            document.getElementById('filter-lab').addEventListener('change', triggerLoad);
            document.getElementById('filter-day').addEventListener('change', triggerLoad);
            document.getElementById('filter-type').addEventListener('change', triggerLoad);
            
            document.getElementById('filter-search').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(triggerLoad, DEBOUNCE_DELAY);
            });
            
            document.getElementById('btn-reset').addEventListener('click', resetFilters);

            // Event Delegation for Load More Button
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('#btn-load-more');
                if (btn) {
                    const nextUrl = btn.getAttribute('data-next-url');
                    if (nextUrl) {
                        loadSchedules(nextUrl, true); // Append mode
                    }
                }
            });

            // Initial load if filters exist in localStorage
            if (filtersLoaded) {
                loadSchedules();
            }

            // ==================== VIEW TOGGLE ====================
            initViewToggle();
        });

        // ==================== TIMETABLE VIEW ====================
        const TT_DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const TT_TIME_START = 5;
        const TT_TIME_END = 23;
        const TT_SLOT_MINUTES = 10;
        const TT_ROW_HEIGHT = 12;
        const TT_TOTAL_SLOTS = (TT_TIME_END - TT_TIME_START) * 60 / TT_SLOT_MINUTES;
        const TT_TYPE_COLORS = {
            'perkuliahan_tetap':       { bg: 'bg-yellow-300', accent: 'bg-yellow-600', border: 'border-yellow-500', shadow: 'shadow-yellow-100', text: 'text-yellow-900' },
            'perkuliahan_tidak_tetap': { bg: 'bg-indigo-400', accent: 'bg-indigo-700', border: 'border-indigo-500', shadow: 'shadow-indigo-100', text: 'text-indigo-900' },
            'non_perkuliahan':         { bg: 'bg-emerald-400', accent: 'bg-emerald-700', border: 'border-emerald-500', shadow: 'shadow-emerald-100', text: 'text-emerald-900' },
            'pribadi':                 { bg: 'bg-orange-400', accent: 'bg-orange-700', border: 'border-orange-500', shadow: 'shadow-orange-100', text: 'text-orange-900' }
        };

        let ttWeekData = null;
        let ttSelectedDay = null;
        let ttSelectedDate = null;
        let ttAllLabs = [];
        let ttWeekOffset = 0;

        // Helpers
        function ttFormatTime(t) {
            if (!t || typeof t !== 'string') return '';
            const match = t.match(/(\d{1,2}):(\d{2})/);
            if (match) return match[1].padStart(2, '0') + ':' + match[2];
            return '';
        }
        function ttTimeToMinutes(t) {
            const str = ttFormatTime(t);
            if (!str) return 0;
            const [h, m] = str.split(':').map(Number);
            return h * 60 + m;
        }
        function ttDateStr(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dd}`;
        }
        function ttExtractId(idStr) {
            // 'sched_123' -> '123'
            return idStr ? idStr.replace('sched_', '') : '';
        }

        // ==================== VIEW TOGGLE ====================
        function initViewToggle() {
            const btnList = document.getElementById('btn-view-list');
            const btnTimetable = document.getElementById('btn-view-timetable');

            btnList.addEventListener('click', () => switchView('list'));
            btnTimetable.addEventListener('click', () => switchView('timetable'));

            // Load saved view
            const savedView = localStorage.getItem('admin_schedule_view') || 'list';
            switchView(savedView);
        }

        function switchView(mode) {
            const listSection = document.getElementById('list-view-section');
            const ttSection = document.getElementById('timetable-view-section');
            const btnList = document.getElementById('btn-view-list');
            const btnTimetable = document.getElementById('btn-view-timetable');

            if (mode === 'timetable') {
                listSection.classList.add('hidden');
                ttSection.classList.remove('hidden');
                btnTimetable.classList.add('bg-white', 'shadow-sm', 'text-yellow-700');
                btnTimetable.classList.remove('text-gray-500');
                btnList.classList.remove('bg-white', 'shadow-sm', 'text-yellow-700');
                btnList.classList.add('text-gray-500');

                // Load timetable data on first switch
                if (!ttWeekData) {
                    ttLoadWeek();
                }
            } else {
                listSection.classList.remove('hidden');
                ttSection.classList.add('hidden');
                btnList.classList.add('bg-white', 'shadow-sm', 'text-yellow-700');
                btnList.classList.remove('text-gray-500');
                btnTimetable.classList.remove('bg-white', 'shadow-sm', 'text-yellow-700');
                btnTimetable.classList.add('text-gray-500');
            }

            localStorage.setItem('admin_schedule_view', mode);
        }

        // ==================== TIMETABLE API ====================
        function ttLoadWeek(targetDate) {
            let url = `{{ route('schedules.week') }}?include_pribadi=1`;
            if (targetDate) {
                url += `&date=${targetDate}`;
            } else if (ttWeekOffset !== 0) {
                url += `&week_offset=${ttWeekOffset}`;
            }

            // Show loading
            document.getElementById('tt-grid-container').innerHTML = `
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-500 mx-auto"></div>
                    <p class="text-gray-500 mt-4">Memuat jadwal...</p>
                </div>`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    ttWeekData = data;
                    ttAllLabs = data.labs || [];

                    // Update week label
                    document.getElementById('tt-week-label').textContent = data.week_label || '';

                    // Build day tabs
                    ttRenderDayTabs(data);

                    // Select appropriate day
                    if (targetDate) {
                        const target = new Date(targetDate + 'T00:00:00');
                        const dow = target.getDay();
                        const idx = dow === 0 ? 5 : dow - 1;
                        if (idx >= 0 && idx < 6) {
                            ttSelectDay(TT_DAYS[idx], targetDate);
                        } else {
                            ttSelectDay(TT_DAYS[0], data.week_start);
                        }
                    } else {
                        const today = new Date();
                        const todayStr = ttDateStr(today);
                        if (todayStr >= data.week_start && todayStr <= data.week_end) {
                            const dow = today.getDay();
                            const idx = dow === 0 ? -1 : dow - 1;
                            if (idx >= 0 && idx < 6) {
                                ttSelectDay(TT_DAYS[idx], todayStr);
                            } else {
                                ttSelectDay(TT_DAYS[0], data.week_start);
                            }
                        } else {
                            ttSelectDay(TT_DAYS[0], data.week_start);
                        }
                    }
                })
                .catch(err => {
                    console.error('Error loading timetable:', err);
                    document.getElementById('tt-grid-container').innerHTML = `
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-red-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-red-600 font-semibold">Gagal memuat jadwal</p>
                        </div>`;
                });
        }

        // ==================== DAY TABS ====================
        function ttRenderDayTabs(data) {
            const container = document.getElementById('tt-day-tabs');
            const ws = new Date(data.week_start + 'T00:00:00');
            let html = '';
            TT_DAYS.forEach((day, idx) => {
                const d = new Date(ws);
                d.setDate(d.getDate() + idx);
                const dayDate = ttDateStr(d);
                const dd = d.getDate();
                const mm = d.getMonth() + 1;
                html += `
                    <button data-day="${day}" data-date="${dayDate}"
                        class="tt-day-tab flex-1 px-4 lg:px-6 py-3 font-semibold text-sm transition border-b-2 border-transparent text-gray-500 hover:text-yellow-600 hover:bg-yellow-50">
                        <div class="flex flex-col items-center">
                            <span>${day}</span>
                            <span class="text-xs font-normal text-gray-400 mt-0.5">${String(dd).padStart(2,'0')}/${String(mm).padStart(2,'0')}</span>
                        </div>
                    </button>`;
            });
            container.innerHTML = html;

            container.querySelectorAll('.tt-day-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    ttSelectDay(this.dataset.day, this.dataset.date);
                });
            });
        }

        function ttSelectDay(day, date) {
            ttSelectedDay = day;
            ttSelectedDate = date;

            // Update tab styles
            document.querySelectorAll('.tt-day-tab').forEach(tab => {
                if (tab.dataset.day === day) {
                    tab.classList.add('border-yellow-500', 'text-yellow-600', 'bg-yellow-50');
                    tab.classList.remove('border-transparent', 'text-gray-500');
                } else {
                    tab.classList.remove('border-yellow-500', 'text-yellow-600', 'bg-yellow-50');
                    tab.classList.add('border-transparent', 'text-gray-500');
                }
            });

            // Filter schedules for selected day
            const daySchedules = (ttWeekData.schedules || []).filter(s => s.date === date);

            ttRenderGrid(daySchedules);
        }

        // ==================== GRID RENDERING ====================
        function ttRenderGrid(schedules) {
            const container = document.getElementById('tt-grid-container');
            if (ttAllLabs.length === 0) {
                container.innerHTML = '<div class="text-center py-12 text-gray-500">Tidak ada lab tersedia</div>';
                return;
            }

            const totalHeight = TT_TOTAL_SLOTS * TT_ROW_HEIGHT;
            let html = '';

            // Header row
            html += `<div class="flex border-b-2 border-gray-300 bg-gray-800 text-white sticky top-0 z-30">`;
            html += '<div class="flex-shrink-0" style="width:70px;"></div>';
            ttAllLabs.forEach(lab => {
                html += `<div class="flex-1 text-center py-3 px-2 font-bold text-sm border-l border-gray-600">${lab.name}</div>`;
            });
            html += '</div>';

            // Grid body
            html += `<div class="flex relative" style="height:${totalHeight}px;">`;

            // Time labels
            html += '<div class="flex-shrink-0 relative bg-gray-50 border-r border-gray-300" style="width:70px;">';
            for (let h = TT_TIME_START; h < TT_TIME_END; h++) {
                const topHour = ((h - TT_TIME_START) * 60 / TT_SLOT_MINUTES) * TT_ROW_HEIGHT;
                html += `<div class="absolute text-xs font-semibold text-gray-500 pr-2 text-right w-full" style="top:${topHour}px; line-height:${TT_ROW_HEIGHT}px;">${String(h).padStart(2,'0')}:00</div>`;
                const topHalf = topHour + (30 / TT_SLOT_MINUTES) * TT_ROW_HEIGHT;
                html += `<div class="absolute text-[10px] text-gray-400 pr-2 text-right w-full" style="top:${topHalf}px; line-height:${TT_ROW_HEIGHT}px;">${String(h).padStart(2,'0')}:30</div>`;
            }
            html += '</div>';

            // Lab columns
            ttAllLabs.forEach(lab => {
                html += `<div class="flex-1 relative border-l border-gray-200" data-lab-id="${lab.id}">`;

                // Gridlines
                for (let slot = 0; slot <= TT_TOTAL_SLOTS; slot++) {
                    const mins = slot * TT_SLOT_MINUTES;
                    const topPx = slot * TT_ROW_HEIGHT;
                    if (mins % 60 === 0) {
                        html += `<div class="absolute w-full border-t border-gray-200" style="top:${topPx}px;"></div>`;
                    } else if (mins % 30 === 0) {
                        html += `<div class="absolute w-full border-t border-dashed border-gray-100" style="top:${topPx}px;"></div>`;
                    } else {
                        html += `<div class="absolute w-full" style="top:${topPx}px; border-top: 1px dotted #e2e8f0;"></div>`;
                    }
                }

                // Schedule blocks
                const labSchedules = schedules.filter(s => s.lab_id === lab.id);
                labSchedules.forEach(s => {
                    const startMin = ttTimeToMinutes(s.start_time);
                    const endMin = ttTimeToMinutes(s.end_time);
                    const startOffset = startMin - (TT_TIME_START * 60);
                    const duration = endMin - startMin;
                    if (startOffset < 0 || duration <= 0) return;

                    const topPx = (startOffset / TT_SLOT_MINUTES) * TT_ROW_HEIGHT;
                    const heightPx = (duration / TT_SLOT_MINUTES) * TT_ROW_HEIGHT;
                    const colors = TT_TYPE_COLORS[s.booking_type] || TT_TYPE_COLORS['perkuliahan_tetap'];
                    const startTimeStr = ttFormatTime(s.start_time);
                    const endTimeStr = ttFormatTime(s.end_time);
                    const isKuliah = s.booking_type === 'perkuliahan_tetap' || s.booking_type === 'perkuliahan_tidak_tetap';
                    const lecturerDisplay = (isKuliah && s.lecturer) ? s.lecturer : '';
                    const scheduleId = ttExtractId(s.id);

                    // Icons
                    const iconClock = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                    const iconUser = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>`;

                    // Tooltip
                    const tooltipParts = [s.course || '-'];
                    if (lecturerDisplay) tooltipParts.push(lecturerDisplay);
                    tooltipParts.push(startTimeStr + ' - ' + endTimeStr);

                    html += `
                        <div class="absolute left-1 right-1 ${colors.bg} rounded-lg shadow-sm border border-gray-200/50 overflow-hidden cursor-pointer hover:shadow-md hover:z-20 transition-all duration-200 group"
                                style="top:${topPx}px; height:${heightPx}px; z-index:5;"
                                title="${tooltipParts.join('\n')}">
                            
                            <!-- Accent Band -->
                            <div class="absolute top-0 bottom-0 left-0 w-2 ${colors.accent}"></div>
                            
                            <!-- Content -->
                            <div class="pl-3 pr-2 py-1.5 h-full flex flex-col justify-start">
                                <!-- Title -->
                                <div class="text-xs font-bold ${colors.text} leading-tight truncate mb-0.5">${s.course || '-'}</div>
                                
                                <!-- Time -->
                                ${heightPx > 35 ? `
                                <div class="flex items-center gap-1.5 text-[10px] font-medium ${colors.text} leading-none mb-0.5 opacity-90">
                                    ${iconClock}
                                    <span class="truncate">${startTimeStr} - ${endTimeStr}</span>
                                </div>` : ''}

                                <!-- Lecturer -->
                                ${heightPx > 50 && lecturerDisplay ? `
                                <div class="flex items-center gap-1.5 text-[10px] ${colors.text} leading-none opacity-80">
                                    ${iconUser}
                                    <span class="truncate">${lecturerDisplay}</span>
                                </div>` : ''}
                            </div>

                            <!-- Admin Action Overlay -->
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-1 rounded-lg z-10">
                                <a href="/admin/schedules/${scheduleId}/edit" class="p-1.5 bg-yellow-400 hover:bg-yellow-500 rounded-md transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5 text-yellow-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <a href="/admin/schedules/${scheduleId}/print" target="_blank" class="p-1.5 bg-blue-400 hover:bg-blue-500 rounded-md transition-colors" title="Cetak">
                                    <svg class="w-3.5 h-3.5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <button type="button" onclick="ttDeleteSchedule(${scheduleId}, '${(s.course || '').replace(/'/g, "\\'")}', event)" class="p-1.5 bg-red-400 hover:bg-red-500 rounded-md transition-colors" title="Hapus">
                                    <svg class="w-3.5 h-3.5 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>`;
                });

                html += '</div>';
            });

            html += '</div>';

            // Empty state
            if (schedules.length === 0) {
                html += `
                    <div class="absolute inset-0 flex items-center justify-center" style="top:50px; pointer-events:none;">
                        <div class="text-center pointer-events-auto">
                            <svg class="w-16 h-16 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-400 font-semibold text-lg">Tidak ada jadwal hari ini</p>
                        </div>
                    </div>`;
            }

            container.innerHTML = html;
        }

        // ==================== DELETE HANDLER ====================
        function ttDeleteSchedule(scheduleId, courseName, event) {
            event.stopPropagation();
            if (!confirm(`Yakin ingin menghapus jadwal "${courseName}"?`)) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            fetch(`/admin/schedules/${scheduleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected || response.ok) {
                    // Reload the timetable data
                    ttLoadWeek(ttSelectedDate);
                } else {
                    throw new Error('Gagal menghapus jadwal');
                }
            })
            .catch(err => {
                console.error('Delete error:', err);
                alert('Gagal menghapus jadwal. Silakan coba lagi.');
            });
        }

        // ==================== WEEK NAVIGATION ====================
        document.getElementById('tt-prev-week').addEventListener('click', () => {
            if (ttWeekData && ttWeekData.week_start) {
                const ws = new Date(ttWeekData.week_start + 'T00:00:00');
                ws.setDate(ws.getDate() - 7);
                ttLoadWeek(ttDateStr(ws));
            }
        });

        document.getElementById('tt-next-week').addEventListener('click', () => {
            if (ttWeekData && ttWeekData.week_start) {
                const ws = new Date(ttWeekData.week_start + 'T00:00:00');
                ws.setDate(ws.getDate() + 7);
                ttLoadWeek(ttDateStr(ws));
            }
        });

        document.getElementById('tt-today-btn').addEventListener('click', () => {
            ttLoadWeek(ttDateStr(new Date()));
        });

        document.getElementById('tt-date-picker').addEventListener('change', function() {
            if (this.value) {
                ttLoadWeek(this.value);
            }
        });
    </script>
</body>
</html>
