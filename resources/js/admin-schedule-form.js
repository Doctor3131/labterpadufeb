const DATE_PATTERN = /^(\d{4})-(\d{2})-(\d{2})$/;
const WEEKDAYS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
const DAY_KEYBOARD_OFFSETS = {
    ArrowLeft: -1,
    ArrowRight: 1,
    ArrowUp: -7,
    ArrowDown: 7,
};

const pad = (value) => String(value).padStart(2, '0');

function parseDate(value) {
    const match = DATE_PATTERN.exec(value || '');
    if (!match) return null;

    const date = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
    return date.getFullYear() === Number(match[1])
        && date.getMonth() === Number(match[2]) - 1
        && date.getDate() === Number(match[3])
        ? date
        : null;
}

function dateValue(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function dateLabel(date) {
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function monthLabel(date) {
    return date.toLocaleDateString('id-ID', {
        month: 'long',
        year: 'numeric',
    });
}

function isBefore(first, second) {
    return first && second && dateValue(first) < dateValue(second);
}

function startOfMondayWeek(date) {
    const result = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const day = result.getDay();
    result.setDate(result.getDate() - (day === 0 ? 6 : day - 1));
    return result;
}

function addDays(date, amount) {
    const result = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    result.setDate(result.getDate() + amount);
    return result;
}

function addMonths(date, amount) {
    return new Date(date.getFullYear(), date.getMonth() + amount, 1);
}

function today() {
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), now.getDate());
}

class ScheduleDatePicker {
    constructor(root) {
        this.root = root;
        this.input = root.querySelector('[data-date-input]') || root.querySelector('input[type="hidden"]');
        this.trigger = root.querySelector('[data-date-trigger]');
        this.popover = root.querySelector('[data-date-popover]');
        this.monthHeading = root.querySelector('[data-date-month]');
        this.weekdays = root.querySelector('[data-date-weekdays]');
        this.grid = root.querySelector('[data-date-grid]');
        this.error = root.querySelector('[data-date-error]');
        this.minInputId = root.dataset.minInput || null;

        if (!this.input || !this.trigger || !this.popover || !this.grid) return;

        const selected = parseDate(this.input.value) || today();
        this.viewDate = new Date(selected.getFullYear(), selected.getMonth(), 1);
        root.__scheduleDatePicker = this;
        this.bindEvents();
        this.render();
        this.updateTrigger();
    }

    bindEvents() {
        this.trigger.addEventListener('click', () => {
            if (this.popover.classList.contains('hidden')) {
                this.open();
            } else {
                this.close();
            }
        });

        this.trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.open();
            }
        });

        this.root.querySelector('[data-date-prev]')?.addEventListener('click', () => {
            this.viewDate = addMonths(this.viewDate, -1);
            this.render();
        });

        this.root.querySelector('[data-date-next]')?.addEventListener('click', () => {
            this.viewDate = addMonths(this.viewDate, 1);
            this.render();
        });

        this.root.querySelector('[data-date-today]')?.addEventListener('click', () => {
            const currentDay = today();
            if (!this.isDisabled(currentDay)) {
                this.viewDate = new Date(currentDay.getFullYear(), currentDay.getMonth(), 1);
                this.chooseDate(currentDay);
            }
        });

        this.root.querySelector('[data-date-clear]')?.addEventListener('click', () => {
            this.input.value = '';
            this.input.dispatchEvent(new Event('change', { bubbles: true }));
            this.close();
        });

        this.grid.addEventListener('click', (event) => {
            const dayButton = event.target.closest('[data-date]');
            if (!dayButton || dayButton.disabled) return;
            this.chooseDate(parseDate(dayButton.dataset.date));
        });

        this.grid.addEventListener('keydown', (event) => {
            const dayButton = event.target.closest('[data-date]');
            if (!dayButton) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                this.close();
                this.trigger.focus();
                return;
            }

            if (event.key === 'PageUp' || event.key === 'PageDown') {
                event.preventDefault();
                this.viewDate = addMonths(this.viewDate, event.key === 'PageUp' ? -1 : 1);
                this.render();
                this.focusDate(parseDate(dayButton.dataset.date));
                return;
            }

            const offset = DAY_KEYBOARD_OFFSETS[event.key];
            if (!offset) return;

            event.preventDefault();
            const targetDate = addDays(parseDate(dayButton.dataset.date), offset);
            if (targetDate.getMonth() !== this.viewDate.getMonth() || targetDate.getFullYear() !== this.viewDate.getFullYear()) {
                this.viewDate = new Date(targetDate.getFullYear(), targetDate.getMonth(), 1);
                this.render();
            }
            this.focusDate(targetDate);
        });

        this.input.addEventListener('change', () => {
            const selected = parseDate(this.input.value);
            if (selected) this.viewDate = new Date(selected.getFullYear(), selected.getMonth(), 1);
            this.updateTrigger();
            this.render();
        });

        document.addEventListener('click', (event) => {
            if (!this.root.contains(event.target)) this.close();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !this.popover.classList.contains('hidden')) {
                this.close();
                this.trigger.focus();
            }
        });
    }

    getMinDate() {
        return this.minInputId ? parseDate(document.getElementById(this.minInputId)?.value) : null;
    }

    isDisabled(date) {
        return Boolean(isBefore(date, this.getMinDate()));
    }

    chooseDate(date) {
        if (!date || this.isDisabled(date)) return;
        this.input.value = dateValue(date);
        this.input.dispatchEvent(new Event('change', { bubbles: true }));
        this.close();
    }

    open() {
        document.querySelectorAll('[data-schedule-date-picker]').forEach((picker) => {
            if (picker !== this.root) picker.__scheduleDatePicker?.close();
        });

        const selected = parseDate(this.input.value);
        if (selected) this.viewDate = new Date(selected.getFullYear(), selected.getMonth(), 1);
        this.popover.classList.remove('hidden');
        this.trigger.setAttribute('aria-expanded', 'true');
        this.render();
    }

    close() {
        this.popover.classList.add('hidden');
        this.trigger.setAttribute('aria-expanded', 'false');
    }

    updateTrigger() {
        const label = this.root.querySelector('[data-date-value]');
        const selected = parseDate(this.input.value);
        const placeholder = this.root.dataset.datePlaceholder || 'Pilih tanggal';
        const fieldLabel = this.root.dataset.dateLabel || 'Tanggal';

        if (label) label.textContent = selected ? dateLabel(selected) : placeholder;
        this.trigger.classList.toggle('is-empty', !selected);
        this.trigger.setAttribute('aria-label', `${fieldLabel}: ${selected ? dateLabel(selected) : placeholder}`);
    }

    render() {
        if (this.monthHeading) this.monthHeading.textContent = monthLabel(this.viewDate);

        if (this.weekdays && !this.weekdays.children.length) {
            WEEKDAYS.forEach((weekday) => {
                const heading = document.createElement('span');
                heading.textContent = weekday;
                heading.setAttribute('aria-hidden', 'true');
                this.weekdays.appendChild(heading);
            });
        }

        this.grid.innerHTML = '';
        const firstDay = startOfMondayWeek(new Date(this.viewDate.getFullYear(), this.viewDate.getMonth(), 1));
        const selectedValue = this.input.value;
        const todayValue = dateValue(today());
        const minDate = this.getMinDate();
        let firstFocusable = null;

        for (let index = 0; index < 42; index += 1) {
            const date = addDays(firstDay, index);
            const value = dateValue(date);
            const isCurrentMonth = date.getMonth() === this.viewDate.getMonth();
            const isSelected = value === selectedValue;
            const disabled = Boolean(minDate && isBefore(date, minDate));
            const dayButton = document.createElement('button');

            dayButton.type = 'button';
            dayButton.dataset.date = value;
            dayButton.className = 'schedule-date-day';
            dayButton.textContent = String(date.getDate());
            dayButton.setAttribute('role', 'gridcell');
            dayButton.setAttribute('aria-label', dateLabel(date));
            dayButton.tabIndex = -1;

            if (!isCurrentMonth) dayButton.classList.add('is-other-month');
            if (value === todayValue) dayButton.classList.add('is-today');
            if (isSelected) {
                dayButton.classList.add('is-selected');
                dayButton.setAttribute('aria-selected', 'true');
            } else {
                dayButton.setAttribute('aria-selected', 'false');
            }

            if (disabled) {
                dayButton.disabled = true;
                dayButton.setAttribute('aria-disabled', 'true');
            }

            if (!disabled && isCurrentMonth && !firstFocusable) firstFocusable = dayButton;
            if (!disabled && (isSelected || (!selectedValue && value === todayValue))) dayButton.tabIndex = 0;
            this.grid.appendChild(dayButton);
        }

        if (!this.grid.querySelector('[tabindex="0"]') && firstFocusable) firstFocusable.tabIndex = 0;

        const todayButton = this.root.querySelector('[data-date-today]');
        if (todayButton) todayButton.disabled = this.isDisabled(today());
    }

    focusDate(date) {
        if (!date) return;
        window.requestAnimationFrame(() => {
            this.grid.querySelector(`[data-date="${dateValue(date)}"]`)?.focus();
        });
    }
}

const MONTH_PICKER_NAMES = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];
const MONTH_VALUE_PATTERN = /^(\d{4})-(\d{2})$/;

function parseMonthValue(value) {
    const match = MONTH_VALUE_PATTERN.exec(value || '');
    if (!match) return null;

    const year = Number(match[1]);
    const month = Number(match[2]);
    return month >= 1 && month <= 12 ? { year, month } : null;
}

function monthInputValue(year, month) {
    return `${year}-${pad(month)}`;
}

function monthInputLabel(value) {
    const parsed = parseMonthValue(value);
    return parsed ? `${MONTH_PICKER_NAMES[parsed.month - 1]} ${parsed.year}` : '';
}

class ScheduleMonthPicker {
    constructor(root) {
        this.root = root;
        this.input = root.querySelector('[data-month-input]') || root.querySelector('input[type="hidden"]');
        this.trigger = root.querySelector('[data-month-trigger]');
        this.popover = root.querySelector('[data-month-popover]');
        this.yearHeading = root.querySelector('[data-month-year]');
        this.grid = root.querySelector('[data-month-grid]');

        if (!this.input || !this.trigger || !this.popover || !this.grid) return;

        const selected = parseMonthValue(this.input.value);
        const current = new Date();
        this.viewYear = selected?.year || current.getFullYear();
        root.__scheduleMonthPicker = this;
        this.bindEvents();
        this.render();
        this.updateTrigger();
    }

    bindEvents() {
        this.trigger.addEventListener('click', () => {
            if (this.popover.classList.contains('hidden')) this.open();
            else this.close();
        });

        this.trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.open();
            }
        });

        this.root.querySelector('[data-month-prev]')?.addEventListener('click', () => {
            this.viewYear -= 1;
            this.render();
        });

        this.root.querySelector('[data-month-next]')?.addEventListener('click', () => {
            this.viewYear += 1;
            this.render();
        });

        this.root.querySelector('[data-month-current]')?.addEventListener('click', () => {
            const current = new Date();
            this.viewYear = current.getFullYear();
            this.chooseMonth(monthInputValue(current.getFullYear(), current.getMonth() + 1));
        });

        this.root.querySelector('[data-month-clear]')?.addEventListener('click', () => {
            this.input.value = '';
            this.input.dispatchEvent(new Event('change', { bubbles: true }));
            this.close();
        });

        this.grid.addEventListener('click', (event) => {
            const monthButton = event.target.closest('[data-month]');
            if (monthButton) this.chooseMonth(monthButton.dataset.month);
        });

        this.grid.addEventListener('keydown', (event) => {
            const months = Array.from(this.grid.querySelectorAll('[data-month]'));
            const currentIndex = months.indexOf(event.target.closest('[data-month]'));
            if (currentIndex < 0) return;

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                months[currentIndex].click();
            } else if (event.key === 'ArrowRight' && months[currentIndex + 1]) {
                event.preventDefault();
                months[currentIndex + 1].focus();
            } else if (event.key === 'ArrowLeft' && months[currentIndex - 1]) {
                event.preventDefault();
                months[currentIndex - 1].focus();
            } else if (event.key === 'ArrowDown' && months[currentIndex + 3]) {
                event.preventDefault();
                months[currentIndex + 3].focus();
            } else if (event.key === 'ArrowUp' && months[currentIndex - 3]) {
                event.preventDefault();
                months[currentIndex - 3].focus();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                this.close();
                this.trigger.focus();
            }
        });

        this.input.addEventListener('change', () => {
            const selected = parseMonthValue(this.input.value);
            if (selected) this.viewYear = selected.year;
            this.updateTrigger();
            this.render();
        });

        document.addEventListener('click', (event) => {
            if (!this.root.contains(event.target)) this.close();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !this.popover.classList.contains('hidden')) {
                this.close();
                this.trigger.focus();
            }
        });
    }

    open() {
        document.querySelectorAll('[data-schedule-month-picker]').forEach((picker) => {
            if (picker !== this.root) picker.__scheduleMonthPicker?.close();
        });
        document.querySelectorAll('[data-schedule-date-picker]').forEach((picker) => picker.__scheduleDatePicker?.close());

        const selected = parseMonthValue(this.input.value);
        if (selected) this.viewYear = selected.year;
        this.popover.classList.remove('hidden');
        this.trigger.setAttribute('aria-expanded', 'true');
        this.render();
    }

    close() {
        this.popover.classList.add('hidden');
        this.trigger.setAttribute('aria-expanded', 'false');
    }

    chooseMonth(value) {
        if (!parseMonthValue(value)) return;
        this.input.value = value;
        this.input.dispatchEvent(new Event('change', { bubbles: true }));
        this.close();
    }

    updateTrigger() {
        const label = this.root.querySelector('[data-month-value]');
        const selectedLabel = monthInputLabel(this.input.value);
        const placeholder = this.root.dataset.monthPlaceholder || 'Pilih bulan';
        const fieldLabel = this.root.dataset.monthLabel || 'Bulan';

        if (label) label.textContent = selectedLabel || placeholder;
        this.trigger.classList.toggle('is-empty', !selectedLabel);
        this.trigger.setAttribute('aria-label', `${fieldLabel}: ${selectedLabel || placeholder}`);
    }

    render() {
        if (this.yearHeading) this.yearHeading.textContent = String(this.viewYear);

        const selected = this.input.value;
        const current = new Date();
        const currentValue = monthInputValue(current.getFullYear(), current.getMonth() + 1);
        this.grid.innerHTML = '';

        MONTH_PICKER_NAMES.forEach((name, index) => {
            const value = monthInputValue(this.viewYear, index + 1);
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.month = value;
            button.className = 'schedule-month-option';
            button.textContent = name;
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', value === selected ? 'true' : 'false');
            button.tabIndex = value === selected || (!selected && value === currentValue) ? 0 : -1;

            if (value === selected) button.classList.add('is-selected');
            if (value === currentValue) button.classList.add('is-current');
            this.grid.appendChild(button);
        });

        if (!this.grid.querySelector('[tabindex="0"]')) {
            this.grid.querySelector('[data-month]')?.setAttribute('tabindex', '0');
        }
    }
}

function dateRequirements() {
    const type = document.getElementById('typeSelect')?.value;
    const frequency = document.querySelector('input[name="schedule_frequency"]:checked')?.value || 'once';
    const scope = document.querySelector('input[name="scope"]:checked')?.value;

    return [
        { id: 'startDate', required: type === 'perkuliahan_tidak_tetap', message: 'Pilih tanggal mulai pelaksanaan.' },
        {
            id: 'endDate',
            required: type === 'perkuliahan_tetap' || (type === 'perkuliahan_tidak_tetap' && frequency === 'multiple'),
            message: 'Pilih tanggal selesai jadwal.',
        },
        {
            id: 'occurrence_date',
            required: scope === 'single' || scope === 'future',
            message: 'Pilih tanggal pertemuan yang akan diubah.',
        },
    ];
}

function setDateError(input, message) {
    const root = input?.closest('[data-schedule-date-picker]');
    const error = root?.querySelector('[data-date-error]');
    const trigger = root?.querySelector('[data-date-trigger]');
    if (!root || !error || !trigger) return;

    error.textContent = message;
    error.classList.remove('hidden');
    trigger.classList.add('is-invalid');
}

function clearDateErrors() {
    document.querySelectorAll('[data-date-error]').forEach((error) => {
        error.textContent = '';
        error.classList.add('hidden');
    });
    document.querySelectorAll('[data-date-trigger]').forEach((trigger) => trigger.classList.remove('is-invalid'));
}

function validateScheduleDates(showRequiredErrors = false) {
    clearDateErrors();
    let firstInvalid = null;

    dateRequirements().forEach(({ id, required, message }) => {
        const input = document.getElementById(id);
        if (!input || !required || input.value) return;
        if (showRequiredErrors) {
            setDateError(input, message);
            firstInvalid ||= input;
        }
    });

    const start = parseDate(document.getElementById('startDate')?.value);
    const endInput = document.getElementById('endDate');
    const end = parseDate(endInput?.value);
    if (start && end && isBefore(end, start)) {
        setDateError(endInput, 'Tanggal selesai harus sama atau setelah tanggal mulai.');
        firstInvalid ||= endInput;
    }

    if (firstInvalid && showRequiredErrors) {
        const trigger = firstInvalid.closest('[data-schedule-date-picker]')?.querySelector('[data-date-trigger]');
        trigger?.focus();
        trigger?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    return !firstInvalid;
}

function initScheduleDatePickers() {
    document.querySelectorAll('[data-schedule-date-picker]').forEach((root) => {
        if (!root.__scheduleDatePicker) new ScheduleDatePicker(root);
    });

    const form = document.getElementById('scheduleForm');
    if (!form || form.dataset.scheduleDateValidationReady) return;
    form.dataset.scheduleDateValidationReady = 'true';

    ['startDate', 'endDate', 'occurrence_date'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', () => validateScheduleDates(false));
    });
    document.getElementById('typeSelect')?.addEventListener('change', () => validateScheduleDates(false));
    document.querySelectorAll('input[name="schedule_frequency"], input[name="scope"]').forEach((input) => {
        input.addEventListener('change', () => validateScheduleDates(false));
    });

    form.addEventListener('submit', (event) => {
        if (!validateScheduleDates(true)) event.preventDefault();
    });
}

function initScheduleMonthPickers() {
    document.querySelectorAll('[data-schedule-month-picker]').forEach((root) => {
        if (!root.__scheduleMonthPicker) new ScheduleMonthPicker(root);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initScheduleMonthPickers();
    initScheduleDatePickers();
});
