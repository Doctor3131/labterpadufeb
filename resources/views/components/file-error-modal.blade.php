{{-- Custom File Validation Error Modal --}}
<div id="file-error-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="file-error-modal-content">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-t-2xl px-6 py-5 text-center">
            <div class="w-16 h-16 mx-auto bg-white/20 rounded-full flex items-center justify-center mb-3">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-white font-bold text-lg">Upload Gagal</h3>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">
            <p id="file-error-modal-message" class="text-gray-700 text-center text-sm leading-relaxed"></p>
            <div class="mt-4 bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-500 text-center">
                    📋 Format: <strong>PDF</strong> &nbsp;|&nbsp; 📦 Maksimal: <strong>5MB</strong>
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 pb-5">
            <button type="button" onclick="closeFileErrorModal()" 
                class="w-full py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-red-500/25">
                Mengerti
            </button>
        </div>
    </div>
</div>

<script>
    function showFileErrorModal(message) {
        const modal = document.getElementById('file-error-modal');
        const content = document.getElementById('file-error-modal-content');
        const msgEl = document.getElementById('file-error-modal-message');
        
        // Clean the message (remove ⚠️ emoji prefix if present)
        msgEl.textContent = message.replace(/^⚠️\s*/, '');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Animate in
        requestAnimationFrame(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        });
    }

    function closeFileErrorModal() {
        const modal = document.getElementById('file-error-modal');
        const content = document.getElementById('file-error-modal-content');
        
        // Animate out
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 200);
    }

    // Close on backdrop click
    document.getElementById('file-error-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeFileErrorModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeFileErrorModal();
        }
    });
</script>
