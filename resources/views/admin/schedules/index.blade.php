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
                    <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-12 sm:h-14 md:h-16 w-auto object-contain">
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
            <form method="GET" class="space-y-3 md:space-y-0 md:flex md:flex-wrap md:gap-4">
                <div class="md:flex-1 md:min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="date" value="{{ request('date') }}" 
                           class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-base md:text-sm">
                </div>
                <div class="md:flex-1 md:min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lab</label>
                    <select name="lab_id" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-base md:text-sm">
                        <option value="">Semua Lab</option>
                        @foreach($labs as $lab)
                            <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:flex-1 md:min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                    <select name="type" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-base md:text-sm">
                        <option value="">Semua Tipe</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 md:items-end">
                    <button type="submit" class="flex-1 md:flex-none px-4 py-2.5 md:py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg text-sm">
                        Filter
                    </button>
                    <a href="{{ route('admin.schedules.index') }}" class="flex-1 md:flex-none px-4 py-2.5 md:py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg text-sm text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Schedule Table/Cards -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Lab</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Hari</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase min-w-[200px]">Mata Kuliah / Kegiatan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase min-w-[150px]">Dosen / PIC</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Tipe</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-800">{{ $schedule->lab->name }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $schedule->day }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $schedule->course }}</div>
                                    @if($schedule->komting && in_array($schedule->type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']))
                                        <div class="text-sm text-gray-500">Komting: {{ $schedule->komting }}</div>
                                    @elseif($schedule->komting && in_array($schedule->type, ['non_perkuliahan', 'pribadi']))
                                        <div class="text-sm text-gray-500">Peminjam: {{ $schedule->komting }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $schedule->lecturer ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $typeColors = [
                                            'perkuliahan_tetap' => 'bg-yellow-100 text-yellow-800',
                                            'perkuliahan_tidak_tetap' => 'bg-indigo-100 text-indigo-800',
                                            'non_perkuliahan' => 'bg-emerald-100 text-emerald-800',
                                            'pribadi' => 'bg-orange-100 text-orange-800',
                                            // Legacy types (for old data)
                                            'booking_recurring' => 'bg-yellow-100 text-yellow-800',
                                            'booking_onetime' => 'bg-gray-100 text-gray-800',
                                        ];
                                        $typeLabels = [
                                            'perkuliahan_tetap' => 'Tetap',
                                            'perkuliahan_tetap' => 'Tetap',
                                            'perkuliahan_tidak_tetap' => 'Tidak Tetap',
                                            'non_perkuliahan' => 'Non Kuliah',
                                            'pribadi' => 'Pribadi',
                                            // Legacy types (for old data)
                                            'booking_recurring' => 'Tetap (Lama)',
                                            'booking_onetime' => 'Sekali (Lama)',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeColors[$schedule->type] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $typeLabels[$schedule->type] ?? $schedule->type }}
                                    </span>
                                    @if($schedule->booking)
                                        <span class="ml-1 text-xs text-gray-400" title="Dari Booking #{{ $schedule->booking_id }}">
                                            (B)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}" 
                                           class="px-3 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg text-sm font-medium">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus jadwal ini?{{ $schedule->booking ? ' Booking terkait akan ditandai sebagai Deleted.' : '' }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Tidak ada jadwal ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-200">
                @forelse($schedules as $schedule)
                    @php
                        $typeColors = [
                            'perkuliahan_tetap' => 'bg-yellow-100 text-yellow-800',
                            'perkuliahan_tidak_tetap' => 'bg-indigo-100 text-indigo-800',
                            'non_perkuliahan' => 'bg-emerald-100 text-emerald-800',
                            'pribadi' => 'bg-orange-100 text-orange-800',
                        ];
                        $typeLabels = [
                            'perkuliahan_tetap' => 'Tetap',
                            'perkuliahan_tidak_tetap' => 'Tidak Tetap',
                            'non_perkuliahan' => 'Non Kuliah',
                            'pribadi' => 'Pribadi',
                        ];
                    @endphp
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800 text-base mb-1">{{ $schedule->course }}</h3>
                                <div class="flex flex-wrap gap-2 items-center text-sm text-gray-600 mb-2">
                                    <span class="font-medium">{{ $schedule->lab->name }}</span>
                                    <span class="text-gray-400">•</span>
                                    <span>{{ $schedule->day }}</span>
                                    <span class="text-gray-400">•</span>
                                    <span>{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap ml-2 {{ $typeColors[$schedule->type] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $typeLabels[$schedule->type] ?? $schedule->type }}
                            </span>
                        </div>
                        
                        @if($schedule->lecturer)
                            <div class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Dosen/PIC:</span> {{ $schedule->lecturer }}
                            </div>
                        @endif
                        
                        @if($schedule->komting && in_array($schedule->type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']))
                            <div class="text-sm text-gray-600 mb-3">
                                <span class="font-medium">Komting:</span> {{ $schedule->komting }}
                            </div>
                        @elseif($schedule->komting && in_array($schedule->type, ['non_perkuliahan', 'pribadi']))
                            <div class="text-sm text-gray-600 mb-3">
                                <span class="font-medium">Peminjam:</span> {{ $schedule->komting }}
                            </div>
                        @endif
                        
                        <div class="flex gap-2 mt-3">
                            <a href="{{ route('admin.schedules.edit', $schedule->id) }}" 
                               class="flex-1 px-4 py-2.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg text-sm font-medium text-center">
                                Edit
                            </a>
                            <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" class="flex-1"
                                  onsubmit="return confirm('Yakin ingin menghapus jadwal ini?{{ $schedule->booking ? ' Booking terkait akan ditandai sebagai Deleted.' : '' }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-4 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Tidak ada jadwal ditemukan
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Summary -->
        <div class="mt-6 text-sm text-gray-500">
            Total: {{ $schedules->count() }} jadwal
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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Target filter selects
            const selects = ['lab_id', 'type'];
            
            selects.forEach(name => {
                const selectElement = document.querySelector(`select[name="${name}"]`);
                if (selectElement) {
                    new CustomSelect(selectElement);
                }
            });
        });
    </script>
</html>
