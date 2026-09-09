@php
    $toastConfig = [
        'success' => [
            'label' => 'Berhasil',
            'class' => 'border-emerald-200 bg-white text-emerald-900',
            'iconClass' => 'bg-emerald-100 text-emerald-700',
            'icon' => 'M5 13l4 4L19 7',
        ],
        'error' => [
            'label' => 'Terjadi masalah',
            'class' => 'border-red-200 bg-white text-red-900',
            'iconClass' => 'bg-red-100 text-red-700',
            'icon' => 'M6 18L18 6M6 6l12 12',
        ],
        'warning' => [
            'label' => 'Perhatian',
            'class' => 'border-yellow-200 bg-white text-yellow-900',
            'iconClass' => 'bg-yellow-100 text-yellow-800',
            'icon' => 'M12 9v2m0 4h.01M12 7a5 5 0 110 10 5 5 0 010-10z',
        ],
        'info' => [
            'label' => 'Informasi',
            'class' => 'border-sky-200 bg-white text-sky-900',
            'iconClass' => 'bg-sky-100 text-sky-700',
            'icon' => 'M12 16v-4m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];

    $toasts = [];
    foreach ($toastConfig as $type => $config) {
        if (session()->has($type)) {
            $toasts[] = ['type' => $type, 'message' => session()->pull($type), 'config' => $config];
        }
    }
@endphp

@if(count($toasts))
    <div class="pointer-events-none fixed inset-x-4 top-4 z-[100] flex flex-col items-end gap-3 sm:left-auto sm:right-6 sm:w-full sm:max-w-md" aria-live="polite" aria-atomic="true">
        @foreach($toasts as $toast)
            <div data-flash-toast class="pointer-events-auto flex w-full items-start gap-3 rounded-xl border p-4 shadow-lg transition duration-200 {{ $toast['config']['class'] }}" role="{{ $toast['type'] === 'error' ? 'alert' : 'status' }}">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $toast['config']['iconClass'] }}" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $toast['config']['icon'] }}" /></svg>
                </span>
                <div class="min-w-0 flex-1 pr-1">
                    <p class="text-sm font-bold">{{ $toast['config']['label'] }}</p>
                    <p class="mt-0.5 text-sm leading-5 text-slate-600">{{ $toast['message'] }}</p>
                </div>
                <button type="button" data-dismiss-flash-toast class="-mr-1 -mt-1 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-400" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('[data-flash-toast]').forEach((toast) => {
            const dismiss = () => {
                toast.classList.add('opacity-0', '-translate-y-2');
                window.setTimeout(() => toast.remove(), 180);
            };
            toast.querySelector('[data-dismiss-flash-toast]')?.addEventListener('click', dismiss);
            window.setTimeout(dismiss, 6000);
        });
    </script>
@endif
