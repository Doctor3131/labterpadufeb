@props(['lab', 'type' => 'perkuliahan_tetap'])

@php
    $colors = [
        'perkuliahan_tetap' => 'bg-yellow-100 text-yellow-800',
        'perkuliahan_tidak_tetap' => 'bg-indigo-100 text-indigo-800',
        'non_perkuliahan' => 'bg-emerald-100 text-emerald-800',
        'pribadi' => 'bg-orange-100 text-orange-800',
    ];

    $class = $colors[$type] ?? 'bg-yellow-100 text-yellow-800';
    
    // Handle null lab (for pribadi bookings without lab assignment)
    $displayLab = $lab ?? '-';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold $class"]) }}>
    {{ $displayLab }}
</span>
