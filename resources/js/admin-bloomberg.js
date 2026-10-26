const initBloombergAdmin = () => {
    const root = document.querySelector('[data-bloomberg-admin]');
    const form = document.getElementById('bloomberg-filters');
    const search = document.getElementById('bloomberg-search');
    const tabs = document.getElementById('bloomberg-type-tabs');
    const results = document.getElementById('bloomberg-results-region');
    const resetLink = document.getElementById('bloomberg-reset-filters');
    const status = document.getElementById('bloomberg-filter-status');
    const error = document.getElementById('bloomberg-filter-error');

    if (!root || !form || !search || !tabs || !results) return;

    const controls = [...form.querySelectorAll('[data-bloomberg-filter-select]')].map((select) => {
        const wrapper = select.closest('[data-bloomberg-custom-select]');
        const trigger = wrapper?.querySelector('[data-bloomberg-select-trigger]');
        const options = wrapper?.querySelector('.custom-select-options');

        if (!wrapper || !trigger || !options) return null;

        return {
            select,
            wrapper,
            trigger,
            options,
            label: trigger.querySelector('span'),
            items: [...options.querySelectorAll('[role="option"]')],
        };
    }).filter(Boolean);

    const validValues = new Map(controls.map(({ select }) => [
        select.name,
        new Set([...select.options].map((option) => option.value)),
    ]));

    let searchTimer;
    let activeController;
    let requestSequence = 0;

    const applyClassVariant = (element, active) => {
        if (!element) return;
        const activeClasses = (element.dataset.activeClasses ?? '').split(/\s+/).filter(Boolean);
        const inactiveClasses = (element.dataset.inactiveClasses ?? '').split(/\s+/).filter(Boolean);
        element.classList.remove(...activeClasses, ...inactiveClasses);
        element.classList.add(...(active ? activeClasses : inactiveClasses));
    };

    const syncControl = (control) => {
        const selectedOption = control.select.selectedOptions[0];
        control.label.textContent = selectedOption?.textContent.trim() ?? '';

        control.items.forEach((option) => {
            const selected = option.dataset.value === control.select.value;
            option.setAttribute('aria-selected', String(selected));
            option.querySelector('[data-bloomberg-check]')?.remove();

            if (!selected) return;

            const check = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            check.setAttribute('data-bloomberg-check', '');
            check.setAttribute('class', 'h-4 w-4 text-indigo-700');
            check.setAttribute('viewBox', '0 0 24 24');
            check.setAttribute('fill', 'none');
            check.setAttribute('stroke', 'currentColor');
            check.setAttribute('aria-hidden', 'true');
            check.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"></path>';
            option.appendChild(check);
        });
    };

    const closeOptions = (control, returnFocus = false) => {
        control.options.classList.add('hidden');
        control.trigger.setAttribute('aria-expanded', 'false');
        if (returnFocus) control.trigger.focus();
    };

    const openOptions = (control, focusSelected = false) => {
        controls.forEach((other) => {
            if (other !== control) closeOptions(other);
        });
        control.options.classList.remove('hidden');
        control.trigger.setAttribute('aria-expanded', 'true');
        if (focusSelected) {
            (control.items.find((option) => option.dataset.value === control.select.value) ?? control.items[0])?.focus();
        }
    };

    const makeFormUrl = () => {
        const url = new URL(form.action, window.location.href);
        new FormData(form).forEach((value, key) => {
            const text = String(value).trim();
            if (text === '' || (key !== 'type' && text === 'all')) url.searchParams.delete(key);
            else url.searchParams.set(key, text);
        });
        url.searchParams.delete('page');
        return url;
    };

    const syncTabLinks = (url) => {
        const activeType = url.searchParams.get('type') || 'all';

        tabs.querySelectorAll('[data-bloomberg-type]').forEach((link) => {
            const selected = link.dataset.bloombergType === activeType;
            const linkUrl = new URL(url);
            linkUrl.searchParams.delete('page');
            linkUrl.searchParams.set('type', link.dataset.bloombergType);
            link.href = linkUrl.href;
            applyClassVariant(link, selected);

            if (selected) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
    };

    const syncControls = (url) => {
        search.value = url.searchParams.get('q') ?? '';
        form.elements.type.value = url.searchParams.get('type') ?? 'all';

        controls.forEach((control) => {
            const allowed = validValues.get(control.select.name);
            const requested = url.searchParams.get(control.select.name);
            control.select.value = allowed.has(requested) ? requested : 'all';
            syncControl(control);
        });

        syncTabLinks(url);

        const hasFilters = search.value.trim() !== '' || controls.some(({ select }) => select.value !== 'all');
        if (resetLink) {
            const resetUrl = new URL(form.action, window.location.href);
            resetUrl.searchParams.set('type', form.elements.type.value);
            resetLink.href = resetUrl.href;
            resetLink.hidden = !hasFilters;
        }
    };

    const updateResults = async (target, historyMode = 'push') => {
        const url = new URL(target, window.location.href);
        const indexUrl = new URL(form.action, window.location.href);
        if (url.origin !== window.location.origin || url.pathname !== indexUrl.pathname) return;

        activeController?.abort();
        const controller = new AbortController();
        activeController = controller;
        const sequence = ++requestSequence;

        results.classList.add('is-loading');
        results.setAttribute('aria-busy', 'true');
        if (error) error.hidden = true;
        if (status) status.textContent = 'Memperbarui hasil Bloomberg.';

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) throw new Error('Filter request failed (' + response.status + ').');

            const payload = await response.json();
            if (typeof payload.html !== 'string') throw new Error('Filter response is missing the results fragment.');
            if (sequence !== requestSequence) return;

            results.innerHTML = payload.html;
            results.classList.add('is-entering');
            if (historyMode === 'push' && url.href !== window.location.href) window.history.pushState({}, '', url);
            else if (historyMode === 'replace' && url.href !== window.location.href) window.history.replaceState({}, '', url);

            syncControls(url);
            if (status) status.textContent = (payload.total ?? 0) + ' permohonan ditampilkan.';
            window.setTimeout(() => {
                if (sequence === requestSequence) results.classList.remove('is-entering');
            }, 260);
        } catch (requestError) {
            if (requestError.name === 'AbortError') return;
            if (sequence === requestSequence) syncControls(new URL(window.location.href));
            if (error) {
                error.textContent = 'Hasil belum berhasil diperbarui. Periksa koneksi lalu coba lagi.';
                error.hidden = false;
            }
            if (status) status.textContent = 'Pembaruan gagal. Hasil sebelumnya tetap ditampilkan.';
        } finally {
            if (sequence === requestSequence) {
                results.classList.remove('is-loading');
                results.setAttribute('aria-busy', 'false');
                activeController = null;
            }
        }
    };

    const chooseOption = (control, option) => {
        if (!option) return;
        const changed = control.select.value !== option.dataset.value;
        control.select.value = option.dataset.value;
        syncControl(control);
        closeOptions(control, true);
        if (changed) updateResults(makeFormUrl(), 'push');
    };

    controls.forEach((control) => {
        control.select.classList.add('custom-select-native');
        control.select.setAttribute('aria-hidden', 'true');
        control.select.tabIndex = -1;
        control.trigger.classList.remove('hidden');
        syncControl(control);

        control.trigger.addEventListener('click', () => {
            if (control.trigger.getAttribute('aria-expanded') === 'true') closeOptions(control);
            else openOptions(control, true);
        });
        control.trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                openOptions(control, true);
            } else if (event.key === 'Escape' && control.trigger.getAttribute('aria-expanded') === 'true') {
                event.preventDefault();
                closeOptions(control);
            }
        });

        control.items.forEach((option, index) => {
            option.addEventListener('click', () => chooseOption(control, option));
            option.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    closeOptions(control, true);
                } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    const direction = event.key === 'ArrowDown' ? 1 : -1;
                    control.items[(index + direction + control.items.length) % control.items.length]?.focus();
                } else if (event.key === 'Home' || event.key === 'End') {
                    event.preventDefault();
                    control.items[event.key === 'Home' ? 0 : control.items.length - 1]?.focus();
                } else if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    chooseOption(control, option);
                }
            });
        });

        control.wrapper.addEventListener('focusout', (event) => {
            if (!control.wrapper.contains(event.relatedTarget)) closeOptions(control);
        });
    });

    document.addEventListener('pointerdown', (event) => {
        controls.forEach((control) => {
            if (!control.wrapper.contains(event.target)) closeOptions(control);
        });
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        window.clearTimeout(searchTimer);
        updateResults(makeFormUrl(), 'push');
    });

    search.addEventListener('input', (event) => {
        if (event.isComposing) return;
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => updateResults(makeFormUrl(), 'replace'), 400);
    });
    search.addEventListener('search', () => {
        window.clearTimeout(searchTimer);
        updateResults(makeFormUrl(), 'replace');
    });

    tabs.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-bloomberg-type]');
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        updateResults(link.href, 'push');
    });

    root.addEventListener('click', (event) => {
        const reset = event.target.closest('[data-bloomberg-reset]');
        if (reset) {
            event.preventDefault();
            updateResults(reset.href, 'push');
            return;
        }

        const pageLink = event.target.closest('#bloomberg-results-region a[href]');
        if (!pageLink || !new URL(pageLink.href, window.location.href).searchParams.has('page')) return;
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        updateResults(pageLink.href, 'push');
    });

    window.addEventListener('popstate', () => updateResults(window.location.href, 'none'));
    syncControls(new URL(window.location.href));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBloombergAdmin, { once: true });
} else {
    initBloombergAdmin();
}
