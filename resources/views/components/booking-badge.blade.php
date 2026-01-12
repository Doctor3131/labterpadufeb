@props(['type'])

@php
    $colors = [
        'perkuliahan_tetap' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        'perkuliahan_tidak_tetap' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
        'non_perkuliahan' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'pribadi' => 'bg-orange-50 text-orange-700 border border-orange-200',
    ];
    
    $labels = [
        'perkuliahan_tetap' => 'Perkuliahan Tetap',
        'perkuliahan_tidak_tetap' => 'Perkuliahan Tidak Tetap',
        'non_perkuliahan' => 'Non-Perkuliahan',
        'pribadi' => 'Pribadi',
    ];

    $class = $colors[$type] ?? 'bg-gray-100 text-gray-600';
    $label = $labels[$type] ?? 'Tidak Diketahui';
@endphp

<span {{ $attributes->merge(['class' => "inline-block px-2 py-1 text-xs rounded $class"]) }}>
    {{ $label }}
</span>
