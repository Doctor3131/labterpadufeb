@extends('layouts.app')

@section('title', 'Laboratorium Terpadu FEB')

@section('content')
<!-- Header with Yellow Background -->
<header class="bg-yellow-500 text-white shadow-md">
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">Lab<span class="text-yellow-200">FEB</span></h1>
                <p class="text-sm text-yellow-100">Laboratorium Terpadu Fakultas Ekonomi dan Bisnis</p>
            </div>
            <div class="space-x-3">
                <a href="{{ route('login') }}" class="inline-block px-6 py-2 bg-white text-yellow-600 font-semibold rounded-lg hover:bg-yellow-50 transition">
                    Login
                </a>
                <a href="{{ route('register') }}" class="inline-block px-6 py-2 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition">
                    Daftar
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="bg-gradient-to-b from-yellow-50 to-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
            Selamat Datang di Laboratorium Terpadu FEB
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-8">
            Fasilitas laboratorium modern untuk mendukung kegiatan akademik dan penelitian mahasiswa Fakultas Ekonomi dan Bisnis
        </p>
        <a href="{{ route('register') }}" class="inline-block px-8 py-3 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition text-lg">
            Reservasi untuk peminjaman ruang
        </a>
    </div>
</section>


      

<!-- Labs Section -->
@if($labs->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h3 class="text-3xl font-bold text-gray-800 text-center mb-12">Ruangan Laboratorium</h3>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            @foreach($labs as $lab)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="h-48 bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center">
                    <span class="text-white text-6xl font-bold">{{ substr($lab->code, 0, 1) }}</span>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xl font-semibold text-gray-800">{{ $lab->name }}</h4>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $lab->status === 'available' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $lab->status === 'occupied' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $lab->status === 'maintenance' ? 'bg-gray-100 text-gray-700' : '' }}
                        ">
                            {{ $lab->status === 'available' ? 'Tersedia' : '' }}
                            {{ $lab->status === 'occupied' ? 'Terpakai' : '' }}
                            {{ $lab->status === 'maintenance' ? 'Maintenance' : '' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">{{ $lab->description }}</p>
                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                        <span>📍 {{ $lab->location }}</span>
                        <span>👥 {{ $lab->capacity }} orang</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Footer -->
<footer class="bg-gray-800 text-white py-8">
    <div class="container mx-auto px-4 text-center">
        <p class="text-gray-400">&copy; {{ date('Y') }} LabTerpaduFEB. All rights reserved.</p>
    </div>
</footer>
@endsection
