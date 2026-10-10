const initAttendanceConfirmation = () => {
    const modal = document.getElementById('refinitiv-attendance-confirm');
    const dialog = modal?.querySelector('[role="alertdialog"]');
    const description = document.getElementById('refinitiv-confirm-description');
    const context = modal?.querySelector('[data-refinitiv-confirm-context]');
    const note = document.getElementById('refinitiv-confirm-note');
    const cancel = modal?.querySelector('[data-refinitiv-confirm-cancel]');
    const confirm = modal?.querySelector('[data-refinitiv-confirm-submit]');

    if (!modal || !dialog || !description || !context || !note || !cancel || !confirm) return;

    let activeForm = null;
    let activeSubmitter = null;
    let previousFocus = null;
    let previousBodyOverflow = '';
    const closeMotionDuration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 320;

    const close = () => {
        modal.classList.remove('is-open');
        modal.classList.add('is-closing');
        window.setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex', 'is-closing');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = previousBodyOverflow;
            previousFocus?.focus({ preventScroll: true });
            activeForm = null;
            activeSubmitter = null;
        }, closeMotionDuration);
    };

    const open = (form, submitter) => {
        activeForm = form;
        activeSubmitter = submitter;
        previousFocus = document.activeElement;
        const isBulk = form.dataset.refinitivConfirm === 'bulk';
        const action = form.dataset.confirmAction || 'Simpan perubahan';

        modal.querySelector('#refinitiv-confirm-title').textContent = action;
        description.textContent = isBulk
            ? 'Periksa nama dan sesi. Pastikan penggunaan sudah berlangsung; sistem tidak membatasi pencatatan berdasarkan waktu.'
            : 'Pastikan perubahan status kehadiran ini sudah sesuai sebelum disimpan.';

        if (isBulk) {
            const selected = [...document.querySelectorAll('[data-refinitiv-row-select]:checked')];
            const names = selected.map((checkbox) => checkbox.dataset.refinitivName || 'Pemohon');
            context.textContent = `${names.length} permohonan:\n${selected.map((checkbox, index) => {
                const dateAndSession = [checkbox.dataset.refinitivDate, checkbox.dataset.refinitivSession].filter(Boolean).join(' · ');
                return `${index + 1}. ${checkbox.dataset.refinitivName || 'Pemohon'}${dateAndSession ? ` — ${dateAndSession}` : ''}`;
            }).join('\n')}`;
        } else {
            const date = form.dataset.confirmDate || '';
            const session = form.dataset.confirmSession || '';
            context.textContent = [form.dataset.confirmName, date, session].filter(Boolean).join('\n');
        }

        note.value = '';
        modal.classList.remove('hidden', 'is-closing');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            modal.classList.add('is-open');
            note.focus({ preventScroll: true });
        });
    };

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-refinitiv-confirm]');
        if (!form) return;

        if (form.dataset.refinitivConfirmed === 'true') {
            delete form.dataset.refinitivConfirmed;
            const submitter = event.submitter;
            if (submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement) {
                submitter.disabled = true;
                submitter.setAttribute('aria-busy', 'true');
            }
            return;
        }

        event.preventDefault();
        open(form, event.submitter);
    });

    cancel.addEventListener('click', close);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
    });

    confirm.addEventListener('click', () => {
        if (!activeForm) return;

        let noteInput = activeForm.querySelector('input[name="note"]');
        if (!noteInput) {
            noteInput = document.createElement('input');
            noteInput.type = 'hidden';
            noteInput.name = 'note';
            activeForm.appendChild(noteInput);
        }
        noteInput.value = note.value.trim();
        activeForm.dataset.refinitivConfirmed = 'true';
        const form = activeForm;
        const submitter = activeSubmitter;
        close();
        window.setTimeout(() => {
            if (submitter && submitter.form === form) form.requestSubmit(submitter);
            else form.requestSubmit();
        }, 0);
    });

    document.addEventListener('keydown', (event) => {
        if (modal.classList.contains('hidden')) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            return;
        }

        if (event.key !== 'Tab') return;
        const focusable = [...dialog.querySelectorAll('button:not([disabled]), textarea:not([disabled])')];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });
};

const initBulkSelection = () => {
    const root = document.querySelector('[data-refinitiv-admin]');
    if (!root) return;

    const sync = () => {
        const checks = [...root.querySelectorAll('[data-refinitiv-row-select]')];
        const selected = checks.filter((checkbox) => checkbox.checked);
        const toolbar = root.querySelector('[data-refinitiv-bulk-toolbar]');
        const count = toolbar?.querySelector('[data-refinitiv-selected-count]');
        const submit = toolbar?.querySelector('button[type="submit"]');
        const selectPage = root.querySelector('[data-refinitiv-select-page]');

        if (toolbar) {
            toolbar.classList.toggle('hidden', selected.length === 0);
            toolbar.classList.toggle('flex', selected.length > 0);
        }
        const selectedCount = String(selected.length);
        // The observer watches this subtree; writing identical text would re-trigger it indefinitely.
        if (count && count.textContent !== selectedCount) count.textContent = selectedCount;
        if (submit) submit.disabled = selected.length === 0;
        if (selectPage) {
            selectPage.checked = checks.length > 0 && selected.length === checks.length;
            selectPage.indeterminate = selected.length > 0 && selected.length < checks.length;
        }
    };

    root.addEventListener('change', (event) => {
        if (event.target.matches('[data-refinitiv-select-page]')) {
            root.querySelectorAll('[data-refinitiv-row-select]').forEach((checkbox) => {
                checkbox.checked = event.target.checked;
            });
            sync();
        } else if (event.target.matches('[data-refinitiv-row-select]')) {
            sync();
        }
    });

    root.addEventListener('click', (event) => {
        if (event.target.closest('[data-refinitiv-reset-link]')) {
            window.setTimeout(sync, 0);
        }
    });

    const resultsRegion = document.getElementById('refinitiv-results-region');
    const observer = new MutationObserver(sync);
    if (resultsRegion) observer.observe(resultsRegion, { childList: true, subtree: true });
    sync();
};

const initRefinitivCalendarLayout = () => {
    const root = document.querySelector('[data-refinitiv-admin]');
    const layout = root?.querySelector('[data-refinitiv-calendar-layout]');
    const calendarRegion = root?.querySelector('[data-refinitiv-calendar-region]');
    const status = root?.querySelector('[data-refinitiv-layout-status]');
    const buttons = [...(root?.querySelectorAll('[data-refinitiv-layout-state]') ?? [])];
    const validStates = new Set(['hidden', 'standard', 'expanded']);

    if (!layout || !calendarRegion || buttons.length === 0) return;

    const messages = {
        hidden: 'Panel kalender disembunyikan. Daftar permohonan menggunakan seluruh lebar.',
        standard: 'Tampilan standar aktif. Kalender dan daftar ditampilkan berdampingan.',
        expanded: 'Panel kalender diperlebar. Lebar daftar menyesuaikan ruang yang tersedia.',
    };

    const setLayout = (state, announce = true) => {
        if (!validStates.has(state)) return;

        const isHidden = state === 'hidden';
        layout.dataset.refinitivCalendarLayout = state;
        calendarRegion.setAttribute('aria-hidden', String(isHidden));
        calendarRegion.toggleAttribute('inert', isHidden);

        buttons.forEach((button) => {
            const isActive = button.dataset.refinitivLayoutState === state;
            const activeClasses = (button.dataset.activeClasses ?? '').split(/\s+/).filter(Boolean);
            const inactiveClasses = (button.dataset.inactiveClasses ?? '').split(/\s+/).filter(Boolean);

            button.classList.remove(...activeClasses, ...inactiveClasses);
            button.classList.add(...(isActive ? activeClasses : inactiveClasses));
            button.setAttribute('aria-pressed', String(isActive));
        });

        if (announce && status) status.textContent = messages[state];
    };

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-refinitiv-layout-state]');
        if (!button || !root.contains(button)) return;

        setLayout(button.dataset.refinitivLayoutState);
    });

    setLayout(layout.dataset.refinitivCalendarLayout, false);
};

const initRefinitivCalendar = () => {
    const calendarRegion = document.querySelector('[data-refinitiv-calendar-region]');
    const form = document.getElementById('refinitiv-filters');
    const periodSelect = form?.querySelector('[name="period"]');

    if (!calendarRegion || !form) return;

    const getCalendar = () => calendarRegion.querySelector('.refinitiv-calendar');
    const getDays = () => {
        try {
            return JSON.parse(calendarRegion.querySelector('[data-refinitiv-calendar-data]')?.textContent || '{}');
        } catch {
            return {};
        }
    };
    const statuses = ['pending', 'hadir', 'tidak_hadir'];
    const sessions = ['sesi_1', 'sesi_2', 'sesi_3'];
    const zeroStatusCounts = () => Object.fromEntries(statuses.map((status) => [status, 0]));
    let animationTimer;
    let dateFilterCleared = false;

    const renderSelectedDate = (button, { filterResults = true, animate = true } = {}) => {
        const calendar = getCalendar();
        const panel = calendar?.querySelector('[data-refinitiv-calendar-panel]');
        const selectedLabel = calendar?.querySelector('[data-refinitiv-selected-date-label]');
        if (!calendar || !panel || !selectedLabel) return;

        const date = button.dataset.refinitivCalendarDay;
        const day = getDays()[date] ?? { total: 0, statuses: zeroStatusCounts(), sessions: {} };
        const dateLabel = button.dataset.refinitivCalendarLabel ?? date;
        const localDate = new Date(date + 'T12:00:00');
        const isFriday = localDate.getDay() === 5;

        form.elements.date.value = date;
        calendar.querySelectorAll('[data-refinitiv-calendar-day]').forEach((dayButton) => {
            const selected = dayButton.dataset.refinitivCalendarDay === date;
            dayButton.classList.toggle('is-selected', selected);
            dayButton.setAttribute('aria-pressed', String(selected));
        });
        const clearDateButton = calendar.querySelector('[data-refinitiv-clear-date]');
        if (clearDateButton) clearDateButton.hidden = false;
        selectedLabel.textContent = dateLabel;
        const totalLabel = calendar.querySelector('[data-refinitiv-day-total]');
        if (totalLabel) totalLabel.textContent = String(day.total ?? 0);

        statuses.forEach((status) => {
            const count = calendar.querySelector('[data-refinitiv-day-status="' + status + '"]');
            if (count) count.textContent = String(day.statuses?.[status] ?? 0);
        });

        sessions.forEach((session, index) => {
            const row = calendar.querySelector('[data-refinitiv-calendar-session="' + session + '"]');
            const sessionData = day.sessions?.[session] ?? { total: 0, statuses: zeroStatusCounts() };
            const total = row?.querySelector('[data-refinitiv-session-total]');
            if (total) total.textContent = String(sessionData.total ?? 0);
            statuses.forEach((status) => {
                const count = row?.querySelector('[data-refinitiv-session-status="' + status + '"]');
                if (count) count.textContent = String(sessionData.statuses?.[status] ?? 0);
            });
            if (index === 2) {
                const time = row?.querySelector('[data-refinitiv-session-time="' + session + '"]');
                if (time) time.textContent = isFriday ? '13.30–15.30 WIB' : '13.00–15.00 WIB';
            }
        });

        if (animate) {
            panel.classList.remove('is-changing');
            window.clearTimeout(animationTimer);
            requestAnimationFrame(() => {
                panel.classList.add('is-changing');
                animationTimer = window.setTimeout(() => panel.classList.remove('is-changing'), 430);
            });
        }

        const filterHint = calendar.querySelector('[data-refinitiv-day-filter-hint]');
        if (filterHint) filterHint.textContent = 'Daftar di samping difilter berdasarkan tanggal ini.';
        if (periodSelect && periodSelect.value !== 'all') {
            periodSelect.value = 'all';
            periodSelect.dispatchEvent(new Event('refinitiv:selection-sync', { bubbles: true }));
        }

        const resultsRegion = document.getElementById('refinitiv-results-region');
        const statusMessage = document.getElementById('refinitiv-filter-status');
        if (filterResults) {
            if (statusMessage) statusMessage.textContent = 'Memfilter permohonan pada ' + dateLabel + '.';
            if (resultsRegion) {
                resultsRegion.classList.add('is-loading');
                resultsRegion.setAttribute('aria-busy', 'true');
            }
            form.dispatchEvent(new CustomEvent('refinitiv:calendar-date-selected', { bubbles: true }));
        }
    };

    const clearDateFilter = () => {
        if (!form.elements.date?.value) return;

        dateFilterCleared = true;
        form.elements.date.value = '';
        const calendar = getCalendar();
        calendar?.querySelectorAll('[data-refinitiv-calendar-day]').forEach((dayButton) => {
            dayButton.classList.remove('is-selected');
            dayButton.setAttribute('aria-pressed', 'false');
        });
        const clearDateButton = calendar?.querySelector('[data-refinitiv-clear-date]');
        if (clearDateButton) clearDateButton.hidden = true;
        const filterHint = calendar?.querySelector('[data-refinitiv-day-filter-hint]');
        if (filterHint) filterHint.textContent = 'Filter tanggal dihapus. Daftar menampilkan semua tanggal.';

        const panel = calendar?.querySelector('[data-refinitiv-calendar-panel]');
        if (panel) {
            panel.classList.remove('is-changing');
            window.clearTimeout(animationTimer);
            requestAnimationFrame(() => {
                panel.classList.add('is-changing');
                animationTimer = window.setTimeout(() => panel.classList.remove('is-changing'), 430);
            });
        }

        form.requestSubmit();
    };

    calendarRegion.addEventListener('click', (event) => {
        const clearButton = event.target.closest('[data-refinitiv-clear-date]');
        if (clearButton) {
            clearDateFilter();
            return;
        }

        const button = event.target.closest('[data-refinitiv-calendar-day]');
        if (!button) return;
        if (form.elements.date.value === button.dataset.refinitivCalendarDay) {
            clearDateFilter();
            return;
        }

        renderSelectedDate(button);
    });

    form.addEventListener('refinitiv:filters-synced', () => {
        const calendar = getCalendar();
        if (!calendar) return;
        const date = form.elements.date?.value ?? '';
        const filterHint = calendar.querySelector('[data-refinitiv-day-filter-hint]');
        const clearDateButton = calendar.querySelector('[data-refinitiv-clear-date]');
        if (!date) {
            calendar.querySelectorAll('[data-refinitiv-calendar-day]').forEach((dayButton) => {
                dayButton.classList.remove('is-selected');
                dayButton.setAttribute('aria-pressed', 'false');
            });
            if (clearDateButton) clearDateButton.hidden = true;
            if (filterHint) {
                filterHint.textContent = dateFilterCleared
                    ? 'Filter tanggal dihapus. Daftar menampilkan semua tanggal.'
                    : 'Ringkasan tanggal ini. Pilih tanggal untuk memfilter daftar di samping.';
            }
            dateFilterCleared = false;
            return;
        }

        if (filterHint) filterHint.textContent = 'Daftar di samping difilter berdasarkan tanggal ini.';
        if (clearDateButton) clearDateButton.hidden = false;
        const selectedButton = calendar.querySelector('[data-refinitiv-calendar-day="' + date + '"]');
        const activeButton = calendar.querySelector('[data-refinitiv-calendar-day][aria-pressed="true"]');
        if (selectedButton && selectedButton !== activeButton) {
            renderSelectedDate(selectedButton, { filterResults: false, animate: false });
        }
    });

    form.addEventListener('refinitiv:selection-sync', () => {
        const wrapper = periodSelect?.closest('.custom-select-wrapper');
        const trigger = wrapper?.querySelector('[data-refinitiv-custom-trigger]');
        const valueLabel = wrapper?.querySelector('[id$="-value"]');
        const selectedOption = periodSelect?.selectedOptions[0];
        if (valueLabel && selectedOption) valueLabel.textContent = selectedOption.textContent.trim();
        wrapper?.querySelectorAll('[role="option"]').forEach((option) => {
            option.setAttribute('aria-selected', String(option.dataset.value === periodSelect?.value));
            option.querySelector('svg')?.remove();
        });
        if (periodSelect && selectedOption) {
            const option = wrapper?.querySelector('[role="option"][data-value="' + periodSelect.value + '"]');
            if (option) {
                const check = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                check.setAttribute('class', 'h-4 w-4 text-blue-700');
                check.setAttribute('viewBox', '0 0 24 24');
                check.setAttribute('fill', 'none');
                check.setAttribute('stroke', 'currentColor');
                check.setAttribute('aria-hidden', 'true');
                check.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"></path>';
                option.appendChild(check);
            }
        }
        trigger?.setAttribute('aria-expanded', 'false');
        wrapper?.querySelector('.custom-select-options')?.classList.add('hidden');
    });
};
const initRefinitivAdmin = () => {
    initAttendanceConfirmation();
    initBulkSelection();
    initRefinitivCalendarLayout();
    initRefinitivCalendar();

    const root = document.querySelector('[data-refinitiv-admin]');
    const form = document.getElementById('refinitiv-filters');
    const search = document.getElementById('refinitiv-search');
    const statusNav = document.getElementById('refinitiv-status-tabs');
    const resultsRegion = document.getElementById('refinitiv-results-region');
    const calendarRegion = document.querySelector('[data-refinitiv-calendar-region]');
    const resetLink = document.getElementById('refinitiv-reset');
    const totalCount = document.getElementById('refinitiv-total-count');
    const statusMessage = document.getElementById('refinitiv-filter-status');
    const filterError = document.getElementById('refinitiv-filter-error');

    if (!root || !form || !search || !statusNav || !resultsRegion) return;

    const validStatuses = new Set(['all', 'pending', 'hadir', 'tidak_hadir']);
    const controls = [...form.querySelectorAll('select[data-refinitiv-custom-select]')].map((select) => {
        const wrapper = select.closest('.custom-select-wrapper');
        const trigger = wrapper?.querySelector('[data-refinitiv-custom-trigger]');
        const options = wrapper?.querySelector('.custom-select-options');
        const label = wrapper?.querySelector('[id$="-value"]');

        if (!wrapper || !trigger || !options || !label) return null;

        return {
            select,
            wrapper,
            trigger,
            options,
            label,
            items: [...options.querySelectorAll('[role="option"]')],
        };
    }).filter(Boolean);

    if (controls.length === 0) return;

    const validValues = new Map(controls.map(({ select }) => [select.name, new Set([...select.options].map((option) => option.value))]));
    let searchTimer;
    let activeController;
    let requestSequence = 0;

    const getStatus = (url) => validStatuses.has(url.searchParams.get('status'))
        ? url.searchParams.get('status')
        : 'pending';

    const applyClassVariant = (element, active) => {
        if (!element) return;
        const activeClasses = (element.dataset.activeClasses ?? '').split(/\s+/).filter(Boolean);
        const inactiveClasses = (element.dataset.inactiveClasses ?? '').split(/\s+/).filter(Boolean);
        element.classList.remove(...activeClasses, ...inactiveClasses);
        element.classList.add(...(active ? activeClasses : inactiveClasses));
    };

    const syncSelection = (control) => {
        const selected = control.select.selectedOptions[0];
        control.label.textContent = selected?.textContent.trim() ?? '';

        control.items.forEach((option) => {
            const isSelected = option.dataset.value === control.select.value;
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

    const syncStatusTabs = (status, url) => {
        statusNav.querySelectorAll('[data-refinitiv-status]').forEach((link) => {
            const active = link.dataset.refinitivStatus === status;
            applyClassVariant(link, active);
            applyClassVariant(link.querySelector('[data-refinitiv-tab-count]'), active);

            const tabUrl = new URL(form.action, window.location.href);
            tabUrl.search = url.search;
            tabUrl.searchParams.delete('page');
            tabUrl.searchParams.set('status', link.dataset.refinitivStatus);
            link.href = tabUrl.href;

            if (active) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
    };

    const makeResetUrl = (status) => {
        const url = new URL(form.action, window.location.href);
        url.searchParams.set('status', status);
        const month = form.elements.month?.value;
        if (month) url.searchParams.set('month', month);
        return url;
    };

    const syncControls = (url) => {
        const status = getStatus(url);
        const query = url.searchParams.get('q') ?? '';
        search.value = query;
        form.querySelector('[name="status"]').value = status;
        if (form.elements.month) form.elements.month.value = url.searchParams.get('month') ?? form.elements.month.defaultValue;
        if (form.elements.date) form.elements.date.value = url.searchParams.get('date') ?? '';

        controls.forEach((control) => {
            const allowed = validValues.get(control.select.name);
            const value = url.searchParams.get(control.select.name);
            control.select.value = allowed.has(value) ? value : control.select.options[0].value;
            syncSelection(control);
        });

        syncStatusTabs(status, url);

        if (resetLink) {
            resetLink.href = makeResetUrl(status).href;
            const sort = form.elements.sort?.value ?? 'schedule_asc';
            const period = form.elements.period?.value ?? 'all';
            const date = form.elements.date?.value ?? '';
            resetLink.hidden = query === '' && sort === 'schedule_asc' && period === 'all' && date === '';
        }

        form.dispatchEvent(new Event('refinitiv:filters-synced'));
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

    const updateResults = async (target, { historyMode = 'push', focusSummary = false, refreshCalendar = false } = {}) => {
        const url = new URL(target, window.location.href);
        const indexUrl = new URL(form.action, window.location.href);
        if (url.origin !== window.location.origin || url.pathname !== indexUrl.pathname) return;

        const requestUrl = new URL(url);
        if (refreshCalendar) requestUrl.searchParams.set('calendar_fragment', '1');

        activeController?.abort();
        const controller = new AbortController();
        activeController = controller;
        const sequence = ++requestSequence;
        resultsRegion.classList.add('is-loading');
        resultsRegion.setAttribute('aria-busy', 'true');
        calendarRegion?.classList.remove('is-entering');
        calendarRegion?.classList.toggle('is-loading', refreshCalendar);
        calendarRegion?.setAttribute('aria-busy', refreshCalendar ? 'true' : 'false');
        if (filterError) filterError.hidden = true;
        if (statusMessage) statusMessage.textContent = 'Memperbarui hasil permohonan.';

        try {
            const response = await fetch(requestUrl, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
            if (!response.ok) throw new Error(`Filter request failed (${response.status}).`);

            const payload = await response.json();
            if (typeof payload.html !== 'string') throw new Error('Filter response is missing the results fragment.');
            if (refreshCalendar && typeof payload.calendarHtml !== 'string') throw new Error('Filter response is missing the calendar fragment.');
            if (sequence !== requestSequence) return;

            resultsRegion.innerHTML = payload.html;
            if (refreshCalendar && calendarRegion) {
                calendarRegion.innerHTML = payload.calendarHtml;
                calendarRegion.classList.add('is-entering');
            }
            resultsRegion.classList.remove('is-loading');
            resultsRegion.classList.add('is-entering');
            resultsRegion.setAttribute('aria-busy', 'false');
            if (totalCount) totalCount.textContent = String(payload.total ?? 0);
            Object.entries(payload.counts ?? {}).forEach(([status, count]) => {
                const badge = statusNav.querySelector(`[data-refinitiv-tab-count="${status}"]`);
                if (badge) badge.textContent = String(count);
            });
            if (historyMode === 'push' && url.href !== window.location.href) window.history.pushState({}, '', url);
            else if (historyMode === 'replace' && url.href !== window.location.href) window.history.replaceState({}, '', url);

            syncControls(url);
            if (statusMessage) statusMessage.textContent = `${payload.total ?? 0} permohonan ditampilkan.`;
            if (focusSummary) resultsRegion.querySelector('[data-refinitiv-results-summary]')?.focus({ preventScroll: true });

            window.setTimeout(() => {
                if (sequence === requestSequence) {
                    resultsRegion.classList.remove('is-entering');
                    calendarRegion?.classList.remove('is-entering');
                }
            }, 520);
        } catch (error) {
            if (error.name === 'AbortError') return;
            if (filterError) {
                filterError.textContent = 'Hasil belum berhasil diperbarui. Periksa koneksi lalu coba lagi.';
                filterError.hidden = false;
            }
            if (statusMessage) statusMessage.textContent = 'Pembaruan gagal. Hasil sebelumnya tetap ditampilkan.';
        } finally {
            if (sequence === requestSequence) {
                resultsRegion.classList.remove('is-loading');
                resultsRegion.setAttribute('aria-busy', 'false');
                calendarRegion?.classList.remove('is-loading');
                calendarRegion?.setAttribute('aria-busy', 'false');
                activeController = null;
            }
        }
    };

    const closeOptions = (control, { returnFocus = false } = {}) => {
        control.options.classList.add('hidden');
        control.trigger.setAttribute('aria-expanded', 'false');
        if (returnFocus) control.trigger.focus();
    };

    const openOptions = (control, focusSelected = false) => {
        control.options.classList.remove('hidden');
        control.trigger.setAttribute('aria-expanded', 'true');
        if (focusSelected) (control.items.find((option) => option.dataset.value === control.select.value) ?? control.items[0])?.focus();
    };

    const chooseOption = (control, option) => {
        if (!option) return;
        const changed = control.select.value !== option.dataset.value;
        control.select.value = option.dataset.value;
        syncSelection(control);
        closeOptions(control, { returnFocus: true });
        if (changed) {
            if (control.select.name === 'period' && form.elements.date) form.elements.date.value = '';
            form.requestSubmit();
        }
    };

    controls.forEach((control) => {
        control.select.classList.add('custom-select-native');
        control.select.setAttribute('aria-hidden', 'true');
        control.select.tabIndex = -1;
        control.trigger.classList.remove('hidden');
        syncSelection(control);

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
                    closeOptions(control, { returnFocus: true });
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

    form.addEventListener('refinitiv:calendar-date-selected', () => {
        window.clearTimeout(searchTimer);
        updateResults(makeFormUrl(), { historyMode: 'push' });
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        window.clearTimeout(searchTimer);
        updateResults(makeFormUrl(), { historyMode: 'push' });
    });
    search.addEventListener('input', (event) => {
        if (event.isComposing) return;
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => updateResults(makeFormUrl(), { historyMode: 'replace' }), 450);
    });
    search.addEventListener('search', () => {
        window.clearTimeout(searchTimer);
        updateResults(makeFormUrl(), { historyMode: 'replace' });
    });
    controls.forEach(({ select }) => select.addEventListener('change', () => {
        if (select.name === 'period' && form.elements.date) form.elements.date.value = '';
        form.requestSubmit();
    }));

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
        const monthLink = event.target.closest('a[data-refinitiv-calendar-nav]');
        if (monthLink && isPlainNavigation(event)) {
            event.preventDefault();
            window.clearTimeout(searchTimer);
            const target = makeFormUrl();
            const month = monthLink.dataset.month;
            const previousMonth = form.elements.month?.value;
            const hadSelectedDate = Boolean(form.elements.date?.value);
            if (month) target.searchParams.set('month', month);
            target.searchParams.delete('date');
            updateResults(target, {
                historyMode: 'push',
                refreshCalendar: Boolean(month && (month !== previousMonth || hadSelectedDate)),
            });
            return;
        }

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

    document.addEventListener('click', (event) => {
        controls.forEach((control) => {
            if (!control.wrapper.contains(event.target)) closeOptions(control);
        });
    });
    window.addEventListener('popstate', () => {
        window.clearTimeout(searchTimer);
        const url = new URL(window.location.href);
        const targetMonth = url.searchParams.get('month') ?? form.elements.month?.defaultValue;
        const refreshCalendar = Boolean(targetMonth && targetMonth !== form.elements.month?.value);
        updateResults(url, { historyMode: 'none', refreshCalendar });
    });

    syncControls(new URL(window.location.href));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRefinitivAdmin, { once: true });
} else {
    initRefinitivAdmin();
}
