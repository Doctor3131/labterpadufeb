<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Berhasil - Peminjaman Barang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl p-8 md:p-12">
            <!-- Success Icon -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Pengajuan Berhasil!</h1>
                <p class="text-gray-600">Permohonan peminjaman barang Anda telah diterima</p>
            </div>

            <!-- Details -->
            <div class="border border-gray-200 rounded-xl p-6 mb-6 space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Nama Peminjam</span>
                    <span class="font-semibold text-gray-800">{{ $borrowing->borrower_name }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Tanggal Pinjam</span>
                    <span class="font-semibold text-gray-800">{{ $borrowing->borrow_date->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Tanggal Kembali</span>
                    <span class="font-semibold text-gray-800">{{ $borrowing->return_date->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Jumlah Barang</span>
                    <span class="font-semibold text-gray-800">{{ $borrowing->borrowedItems->count() }} item</span>
                </div>
            </div>

            <!-- Items List -->
            <div class="mb-6">
                <h3 class="font-bold text-gray-800 mb-3">Barang yang Dipinjam:</h3>
                <div class="space-y-2">
                    @php
                        $groupedItems = $borrowing->borrowedItems->groupBy(function($item) {
                            return $item->item->category ?? $item->item->name;
                        })->map(function($items) {
                            $first = $items->first();
                            return [
                                'name' => $first->item->category ?? $first->item->name,
                                'quantity' => $items->sum('quantity'),
                            ];
                        });
                    @endphp
                    @foreach($groupedItems as $item)
                        <div class="bg-gray-50 rounded-lg p-3 flex justify-between items-center">
                            <span class="text-gray-800 font-medium block">{{ $item['name'] }}</span>
                            <span class="text-sm text-gray-600">{{ $item['quantity'] }} unit</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('asset-borrowing.create') }}" 
                    class="flex-1 text-center bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-bold transition-all shadow-md hover:shadow-lg">
                    Ajukan Lagi
                </a>
                <a href="{{ route('landing') }}" 
                    class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-bold transition-all">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
