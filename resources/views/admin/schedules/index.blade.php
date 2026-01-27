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
            <a href="{{ route('admin.schedules.create') }}" 
               class="flex items-center justify-center px-4 py-3 md:py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all text-sm md:text-base w-full md:w-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Jadwal
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg">
                {{ session('success') }}
            </div>
        @endif

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
        });
    </script>
</body>
</html>
