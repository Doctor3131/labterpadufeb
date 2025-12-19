@extends('layouts.app')

@section('title', 'Login - LabTerpaduFEB')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gray-50">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold mb-2 text-gray-800">
                Lab<span class="text-yellow-500">FEB</span>
            </h1>
            <p class="text-gray-600">Masuk ke akun Anda</p>
        </div>

        <!-- Login Card - Minimalist Style -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition"
                        placeholder="email@example.com"
                    >
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 text-yellow-500 border-gray-300 rounded focus:ring-yellow-500"
                        >
                        <span class="ml-2 text-sm text-gray-600">Ingat Saya</span>
                    </label>
                </div>

                <!-- Login Button -->
                <button 
                    type="submit"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-4 rounded-lg transition duration-200"
                >
                    Masuk
                </button>

                <!-- Back to Home -->
                <div class="text-center mt-6">
                    <a href="{{ route('landing') }}" class="text-sm text-gray-500 hover:text-gray-700">
                        ← Kembali ke Beranda
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
