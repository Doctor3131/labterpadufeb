<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <title>@yield('title', 'Dashboard Admin - Laboratorium dan Fasilitas Digital FEB UNDIP')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        /* Smooth transitions */
        * {
            transition: all 0.2s ease;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50">
    <!-- Modern Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 border-b-4 border-yellow-500">
        <div class="container mx-auto px-3 md:px-4 py-2 md:py-3">
            <div class="flex justify-between items-center">
                <!-- Branding -->
                <div class="flex items-center">
                    <a href="{{ route('landing') }}">
                        <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-10 md:h-12 w-auto object-contain">
                    </a>
                </div>

                <!-- Desktop Menu (Hidden on Mobile) -->
                <div class="hidden lg:flex items-center space-x-1.5">
                    <!-- Lihat Jadwal (standalone) -->
                    <a href="{{ route('landing') }}" class="px-2.5 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition-all flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Lihat Jadwal
                    </a>

                    <!-- Dropdown: Kelola -->
                    <div class="relative" id="dropdown-kelola">
                        <button onclick="toggleDropdown('dropdown-kelola')" type="button" class="px-2.5 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Kelola
                            <svg class="w-3.5 h-3.5 ml-1 drop-arrow transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="drop-menu hidden absolute top-full left-0 mt-1.5 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50">
                            <a href="{{ route('admin.schedules.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 {{ request()->routeIs('admin.schedules.*') ? 'bg-green-50 text-green-700 font-semibold' : '' }}">
                                <svg class="w-4 h-4 mr-2.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Kelola Jadwal
                            </a>
                            <a href="{{ route('admin.labs.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 {{ request()->routeIs('admin.labs.*') ? 'bg-orange-50 text-orange-700 font-semibold' : '' }}">
                                <svg class="w-4 h-4 mr-2.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Kelola Ruang
                            </a>
                            <a href="{{ route('admin.inventories.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 {{ request()->routeIs('admin.inventories.*') ? 'bg-purple-50 text-purple-700 font-semibold' : '' }}">
                                <svg class="w-4 h-4 mr-2.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                Inventaris
                            </a>
                        </div>
                    </div>

                    <!-- Dropdown: Data & Laporan -->
                    <div class="relative" id="dropdown-data">
                        <button onclick="toggleDropdown('dropdown-data')" type="button" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Data & Laporan
                            <svg class="w-3.5 h-3.5 ml-1 drop-arrow transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="drop-menu hidden absolute top-full left-0 mt-1.5 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50">
                            <a href="{{ route('admin.reports.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('admin.reports.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
                                <svg class="w-4 h-4 mr-2.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Laporan
                            </a>
                            <a href="{{ route('admin.bps.master-data.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 {{ request()->routeIs('admin.bps.master-data.*') || request()->routeIs('admin.bps.sub-data.*') ? 'bg-teal-50 text-teal-700 font-semibold' : '' }}">
                                <svg class="w-4 h-4 mr-2.5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                </svg>
                                Master Data BPS
                            </a>
                        </div>
                    </div>

                    <!-- Dropdown: Lainnya -->
                    <div class="relative" id="dropdown-lainnya">
                        <button onclick="toggleDropdown('dropdown-lainnya')" type="button" class="px-2.5 py-1.5 bg-pink-600 hover:bg-pink-700 text-white rounded-lg text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                            </svg>
                            Lainnya
                            <svg class="w-3.5 h-3.5 ml-1 drop-arrow transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="drop-menu hidden absolute top-full right-0 mt-1.5 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50">
                            <a href="{{ route('admin.feedbacks.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-700 {{ request()->routeIs('admin.feedbacks.*') ? 'bg-pink-50 text-pink-700 font-semibold' : '' }}">
                                <svg class="w-4 h-4 mr-2.5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Feedback
                            </a>
                            <a href="{{ route('admin.announcements.index') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-700 {{ request()->routeIs('admin.announcements.*') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                                <svg class="w-4 h-4 mr-2.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                                Pengumuman
                            </a>
                        </div>
                    </div>

                    <!-- Kelola User (Super Admin only) -->
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.users.index') }}" class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-all flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            Kelola User
                        </a>
                    @endif

                    <!-- Profile -->
                    <a href="{{ route('profile.show') }}" class="flex items-center space-x-1.5 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-xs font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    </a>

                    <!-- Logout -->
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Yakin ingin logout?')" class="px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-all flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden flex items-center">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-2 rounded-md text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100 bg-white">
            <div class="px-4 pt-2 pb-4 space-y-2">
                <a href="{{ route('profile.show') }}" class="flex items-center space-x-3 px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg mb-2 transition-all">
                    <div class="bg-yellow-100 p-2 rounded-full">
                         <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700 block">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-gray-500">Pengaturan Akun</span>
                    </div>
                </a>

                <a href="{{ route('landing') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-yellow-600 hover:bg-yellow-50">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Lihat Jadwal
                </a>

                <a href="{{ route('admin.schedules.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.schedules.*') ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }}">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Kelola Jadwal
                </a>

                <a href="{{ route('admin.labs.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.labs.*') ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50' }}">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Kelola Ruang
                </a>

                 <a href="{{ route('admin.inventories.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.inventories.*') ? 'text-purple-600 bg-purple-50' : 'text-gray-600 hover:text-purple-600 hover:bg-purple-50' }}">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Inventaris
                </a>

                <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.reports.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50' }}">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Laporan
                </a>

                <a href="{{ route('admin.bps.master-data.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.bps.master-data.*') || request()->routeIs('admin.bps.sub-data.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-teal-600 hover:bg-teal-50' }}">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                    Master Data BPS
                </a>

                <a href="{{ route('admin.feedbacks.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.feedbacks.*') ? 'text-pink-600 bg-pink-50' : 'text-gray-600 hover:text-pink-600 hover:bg-pink-50' }}">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Feedback
                </a>

                <a href="{{ route('admin.announcements.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.announcements.*') ? 'text-amber-600 bg-amber-50' : 'text-gray-600 hover:text-amber-600 hover:bg-amber-50' }}">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    Pengumuman
                </a>

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.users.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        Kelola User
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Yakin ingin logout?')" class="w-full text-left block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Dropdown Toggle Script -->
    <script>
        function toggleDropdown(id) {
            var allDropdowns = document.querySelectorAll('.drop-menu');
            var allArrows = document.querySelectorAll('.drop-arrow');
            var target = document.getElementById(id);
            var menu = target.querySelector('.drop-menu');
            var arrow = target.querySelector('.drop-arrow');
            var isOpen = !menu.classList.contains('hidden');

            // Close all dropdowns first
            allDropdowns.forEach(function(d) { d.classList.add('hidden'); });
            allArrows.forEach(function(a) { a.style.transform = ''; });

            // Toggle the clicked one
            if (!isOpen) {
                menu.classList.remove('hidden');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            var dropdowns = ['dropdown-kelola', 'dropdown-data', 'dropdown-lainnya'];
            var clickedInside = false;
            dropdowns.forEach(function(id) {
                var el = document.getElementById(id);
                if (el && el.contains(e.target)) clickedInside = true;
            });
            if (!clickedInside) {
                document.querySelectorAll('.drop-menu').forEach(function(d) { d.classList.add('hidden'); });
                document.querySelectorAll('.drop-arrow').forEach(function(a) { a.style.transform = ''; });
            }
        });
    </script>

    <div class="container mx-auto px-4 md:px-6 py-4 md:py-8 max-w-7xl">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
