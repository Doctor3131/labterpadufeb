<div id="refinitiv-attendance-confirm" class="refinitiv-confirm-modal fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/45 p-4" aria-hidden="true">
    <section class="refinitiv-confirm-panel w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl sm:p-6" role="alertdialog" aria-modal="true" aria-labelledby="refinitiv-confirm-title" aria-describedby="refinitiv-confirm-description" tabindex="-1">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
            </span>
            <div class="min-w-0">
                <h2 id="refinitiv-confirm-title" class="text-base font-semibold text-slate-900">Konfirmasi perubahan kehadiran</h2>
                <p id="refinitiv-confirm-description" class="mt-1 text-sm leading-5 text-slate-600"></p>
            </div>
        </div>
        <div class="mt-4 max-h-40 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3">
            <p data-refinitiv-confirm-context class="whitespace-pre-line text-sm font-medium leading-5 text-slate-800"></p>
        </div>
        <label for="refinitiv-confirm-note" class="mt-4 block text-sm font-medium text-slate-700">Catatan riwayat <span class="font-normal text-slate-500">(opsional)</span></label>
        <textarea id="refinitiv-confirm-note" rows="3" maxlength="1000" class="mt-1.5 w-full resize-y rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Tambahkan alasan atau konteks perubahan"></textarea>
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" data-refinitiv-confirm-cancel class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">Batal</button>
            <button type="button" data-refinitiv-confirm-submit class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">Konfirmasi</button>
        </div>
    </section>
</div>
