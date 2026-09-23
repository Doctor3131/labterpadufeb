const initRefinitivAdmin = () => {
    const root = document.querySelector('[data-refinitiv-admin]');
    const form = document.getElementById('refinitiv-filters');
    const search = document.getElementById('refinitiv-search');
    const select = document.getElementById('refinitiv-sort');
    const trigger = document.querySelector('[data-refinitiv-sort-trigger]');
    const options = document.getElementById('refinitiv-sort-options');
    const wrapper = select?.closest('.custom-select-wrapper');
    const statusNav = document.getElementById('refinitiv-status-tabs');
    const resultsRegion = document.getElementById('refinitiv-results-region');
    const resetLink = document.getElementById('refinitiv-reset');
    const totalCount = document.getElementById('refinitiv-total-count');
    const statusMessage = document.getElementById('refinitiv-filter-status');

    if (!root || !form || !search || !select || !trigger || !options || !wrapper || !statusNav || !resultsRegion) return;

    const optionItems = [...options.querySelectorAll('[role="option"]')];
    const sortLabel = document.getElementById('refinitiv-sort-value');
    const validStatuses = new Set(['all', 'pending', 'hadir', 'tidak_hadir']);
    const validSorts = new Set(['schedule_asc', 'schedule_desc', 'recent', 'name_asc']);
    let searchTimer;
    let activeController;
    let requestSequence = 0;

    const getStatus = (url) => validStatuses.has(url.searchParams.get('status'))
        ? url.searchParams.get('status')
        : 'pending';

    const syncSelection = () => {
        const selected = select.selectedOptions[0];
        if (sortLabel) sortLabel.textContent = selected?.textContent.trim() ?? '';

        optionItems.forEach((option) => {
            const isSelected = option.dataset.value === select.value;
            option.setAttribute('aria-selected', String(isSelected));
            option.querySelector('svg')?.remove();

            if (isSelected) {
                const checkmark = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                checkmark.setAttribute('class', 'h-4 w-4 text-blue-700');
                checkmark.setAttribute('viewBox', '0 0 24 24');
                checkmark.setAttribute('fill', 'none');
                checkmark.setAttribute('stroke', 'currentColor');
                checkmark.setAttribute('aria-hidden', 'true');
                checkmark.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"></path>';
                option.appendChild(checkmark);
            }
        });
    };

    const applyClassVariant = (element, active) => {
        if (!element) return;
        const activeClasses = (element.dataset.activeClasses ?? '').split(/\s+/).filter(Boolean);
        const inactiveClasses = (element.dataset.inactiveClasses ?? '').split(/\s+/).filter(Boolean);
        element.classList.remove(...activeClasses, ...inactiveClasses);
        element.classList.add(...(active ? activeClasses : inactiveClasses));
    };

    const syncStatusTabs = (status, query, sort) => {
        statusNav.querySelectorAll('[data-refinitiv-status]').forEach((link) => {
            const active = link.dataset.refinitivStatus === status;
            applyClassVariant(link, active);
            applyClassVariant(link.querySelector('[data-refinitiv-tab-count]'), active);

            const tabUrl = new URL(form.action, window.location.href);
            tabUrl.searchParams.set('status', link.dataset.refinitivStatus);
            tabUrl.searchParams.set('sort', sort);
            if (query !== '') tabUrl.searchParams.set('q', query);
            link.href = tabUrl.href;

            if (active) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
    };

    const makeResetUrl = (status) => {
        const url = new URL(form.action, window.location.href);
        url.searchParams.set('status', status);
        return url;
    };

    const syncControls = (url) => {
        const status = getStatus(url);
        const query = url.searchParams.get('q') ?? '';
        const sort = validSorts.has(url.searchParams.get('sort'))
            ? url.searchParams.get('sort')
            : 'schedule_asc';

        search.value = query;
        select.value = sort;
        form.querySelector('[name="status"]').value = status;
        syncSelection();
        syncStatusTabs(status, query, sort);

        if (resetLink) {
            resetLink.href = makeResetUrl(status).href;
            resetLink.hidden = query === '' && sort === 'schedule_asc';
        }
    };

    const makeFormUrl = () => {
        const url = new URL(form.action, window.location.href);
        new FormData(form).forEach((value, key) => {
            const text = String(value).trim();
            if (text !== '') url.searchParams.set(key, text);
            else url.searchParams.delete(key);
        });
        url.searchParams.delete('page');
        return url;
    };

    const isPlainNavigation = (event) => event.button === 0
        && !event.metaKey
        && !event.ctrlKey
        && !event.shiftKey
        && !event.altKey;

    const updateResults = async (target, { historyMode = 'push', focusSummary = false } = {}) => {
        const url = new URL(target, window.location.href);
        const indexUrl = new URL(form.action, window.location.href);

        if (url.origin !== window.location.origin || url.pathname !== indexUrl.pathname) return;

        activeController?.abort();
        const controller = new AbortController();
        activeController = controller;
        const sequence = ++requestSequence;

        resultsRegion.classList.add('is-loading');
        resultsRegion.setAttribute('aria-busy', 'true');
        if (statusMessage) statusMessage.textContent = 'Memperbarui hasil permohonan.';

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controller.signal,
            });

            if (!response.ok) throw new Error(`Filter request failed (${response.status}).`);

            const payload = await response.json();
            if (typeof payload.html !== 'string') throw new Error('Filter response is missing the results fragment.');
            if (sequence !== requestSequence) return;

            resultsRegion.innerHTML = payload.html;
            resultsRegion.classList.remove('is-loading');
            resultsRegion.classList.add('is-entering');
            resultsRegion.setAttribute('aria-busy', 'false');

            if (totalCount) totalCount.textContent = String(payload.total ?? 0);
            if (historyMode === 'push' && url.href !== window.location.href) window.history.pushState({}, '', url);
            else if (historyMode === 'replace' && url.href !== window.location.href) window.history.replaceState({}, '', url);

            syncControls(url);
            if (statusMessage) statusMessage.textContent = `${payload.total ?? 0} permohonan ditampilkan.`;

            if (focusSummary) {
                resultsRegion.querySelector('[data-refinitiv-results-summary]')?.focus({ preventScroll: true });
            }

            window.setTimeout(() => {
                if (sequence === requestSequence) resultsRegion.classList.remove('is-entering');
            }, 320);
        } catch (error) {
            if (error.name === 'AbortError') return;
            window.location.assign(url.href);
        } finally {
            if (sequence === requestSequence) {
                resultsRegion.classList.remove('is-loading');
                resultsRegion.setAttribute('aria-busy', 'false');
                activeController = null;
            }
        }
    };

    const closeOptions = ({ returnFocus = false } = {}) => {
        options.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
        if (returnFocus) trigger.focus();
    };

    const openOptions = (focusSelected = false) => {
        options.classList.remove('hidden');
        trigger.setAttribute('aria-expanded', 'true');

        if (focusSelected) {
            (optionItems.find((option) => option.dataset.value === select.value) ?? optionItems[0])?.focus();
        }
    };

    const chooseOption = (option) => {
        if (!option) return;
        const changed = select.value !== option.dataset.value;
        select.value = option.dataset.value;
        syncSelection();
        closeOptions({ returnFocus: true });
        if (changed) form.requestSubmit();
    };

    select.classList.add('custom-select-native');
    select.setAttribute('aria-hidden', 'true');
    select.tabIndex = -1;
    trigger.classList.remove('hidden');
    syncSelection();

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        window.clearTimeout(searchTimer);
        updateResults(makeFormUrl(), { historyMode: 'push' });
    });

    search.addEventListener('input', (event) => {
        if (event.isComposing) return;
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            updateResults(makeFormUrl(), { historyMode: 'replace' });
        }, 450);
    });

    search.addEventListener('search', () => {
        window.clearTimeout(searchTimer);
        updateResults(makeFormUrl(), { historyMode: 'replace' });
    });

    select.addEventListener('change', () => form.requestSubmit());

    statusNav.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-refinitiv-status]');
        if (!link || !isPlainNavigation(event)) return;
        event.preventDefault();
        window.clearTimeout(searchTimer);
        const target = makeFormUrl();
        target.searchParams.set('status', link.dataset.refinitivStatus);
        updateResults(target, { historyMode: 'push' });
    });

    root.addEventListener('click', (event) => {
        const reset = event.target.closest('[data-refinitiv-reset-link]');
        if (reset && isPlainNavigation(event)) {
            event.preventDefault();
            window.clearTimeout(searchTimer);
            updateResults(reset.href, { historyMode: 'push' });
            return;
        }

        const pageLink = event.target.closest('[data-refinitiv-pagination] a[href]');
        if (pageLink && isPlainNavigation(event)) {
            event.preventDefault();
            window.clearTimeout(searchTimer);
            const pageUrl = new URL(pageLink.href, window.location.href);
            const target = makeFormUrl();
            const page = pageUrl.searchParams.get('page');
            if (page) target.searchParams.set('page', page);
            updateResults(target, { historyMode: 'push', focusSummary: true });
        }
    });

    trigger.addEventListener('click', () => {
        const isOpen = trigger.getAttribute('aria-expanded') === 'true';
        if (isOpen) closeOptions();
        else openOptions(true);
    });

    trigger.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            openOptions(true);
        } else if (event.key === 'Escape' && trigger.getAttribute('aria-expanded') === 'true') {
            event.preventDefault();
            closeOptions();
        }
    });

    optionItems.forEach((option, index) => {
        option.addEventListener('click', () => chooseOption(option));
        option.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                closeOptions({ returnFocus: true });
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                optionItems[(index + direction + optionItems.length) % optionItems.length]?.focus();
            } else if (event.key === 'Home' || event.key === 'End') {
                event.preventDefault();
                optionItems[event.key === 'Home' ? 0 : optionItems.length - 1]?.focus();
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                chooseOption(option);
            }
        });
    });

    wrapper.addEventListener('focusout', (event) => {
        if (!wrapper.contains(event.relatedTarget)) closeOptions();
    });

    document.addEventListener('click', (event) => {
        if (!wrapper.contains(event.target)) closeOptions();
    });

    window.addEventListener('popstate', () => {
        window.clearTimeout(searchTimer);
        const url = new URL(window.location.href);
        syncControls(url);
        updateResults(url, { historyMode: 'none' });
    });

    syncControls(new URL(window.location.href));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRefinitivAdmin, { once: true });
} else {
    initRefinitivAdmin();
}
