<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Peminjaman Data - Laboratorium dan Fasilitas Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        .animation-delay-100 { animation-delay: 0.1s; opacity: 0; }
        .animation-delay-200 { animation-delay: 0.2s; opacity: 0; }
        .animation-delay-300 { animation-delay: 0.3s; opacity: 0; }
        .animation-delay-400 { animation-delay: 0.4s; opacity: 0; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100 min-h-screen flex flex-col">
    
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <a href="{{ route('landing') }}" class="flex items-center space-x-3 group cursor-pointer">
                    <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-12 lg:h-16 w-auto object-contain group-hover:scale-105 transition-all duration-300">
                </a>
                <a href="{{ route('landing') }}" class="px-4 py-2 text-gray-600 hover:text-yellow-600 font-semibold rounded-lg hover:bg-yellow-50 transition-all duration-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-yellow-500 via-yellow-400 to-orange-400 text-white overflow-hidden pb-24 lg:pb-32">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="container mx-auto px-4 lg:px-8 pt-12 lg:pt-16 pb-12 lg:pb-16 relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <h1 class="text-3xl lg:text-4xl font-bold mb-4 drop-shadow-md animate-fade-in-up animation-delay-100">
                        Peminjaman Data
                    </h1>
                    <p class="text-lg text-yellow-50 drop-shadow animate-fade-in-up animation-delay-200">
                        Pilih jenis data yang ingin Anda pinjam
                    </p>
                </div>
            </div>
            <!-- Wave Decoration -->
            <div class="absolute bottom-0 left-0 right-0 -mb-1">
                <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 50L48 45.7C96 41.3 192 32.7 288 30.2C384 27.7 480 31.3 576 39.8C672 48.3 768 61.7 864 65.8C960 70 1056 65 1152 55.2C1248 45.3 1344 30.7 1392 23.3L1440 16V100H1392C1344 100 1248 100 1152 100C1056 100 960 100 864 100C768 100 672 100 576 100C480 100 384 100 288 100C192 100 96 100 48 100H0V50Z" fill="#f8fafc"/>
                </svg>
            </div>
        </section>

        <!-- Main Content -->
        <section class="pb-12 pt-0 lg:pb-16 lg:pt-0">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    
                    <!-- Card Refinitiv -->
                    <a href="{{ route('refinitiv.create') }}" class="group block animate-fade-in-up animation-delay-300">
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-blue-200 transform hover:-translate-y-2">
                            <!-- Header with Icon -->
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 lg:p-8">
                                <div class="w-16 h-16 lg:w-20 lg:h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-8 h-8 lg:w-10 lg:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl lg:text-3xl font-bold text-white">Refinitiv</h2>
                                <p class="text-blue-100 mt-2">Data Pasar Keuangan</p>
                            </div>
                            
                            <!-- Content -->
                            <div class="p-6">
                                <div class="flex items-center text-blue-600 font-semibold group-hover:text-blue-700">
                                    <span>Ajukan Peminjaman</span>
                                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- Card BPS -->
                    <a href="{{ route('bps.create') }}" class="group block animate-fade-in-up animation-delay-400">
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-teal-200 transform hover:-translate-y-2">
                            <!-- Header with Icon -->
                            <div class="bg-gradient-to-br from-teal-500 to-teal-600 p-6 lg:p-8">
                                <div class="w-16 h-16 lg:w-20 lg:h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-8 h-8 lg:w-10 lg:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl lg:text-3xl font-bold text-white">Data BPS</h2>
                                <p class="text-teal-100 mt-2">Badan Pusat Statistik</p>
                            </div>
                            
                            <!-- Content -->
                            <div class="p-6">
                                <div class="flex items-center text-teal-600 font-semibold group-hover:text-teal-700">
                                    <span>Ajukan Permintaan</span>
                                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>

                </div>
            </div>
        </div>
    </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} Laboratorium dan Fasilitas Digital FEB UNDIP. All rights reserved.
        </div>
    </footer>

</body>
</html>
