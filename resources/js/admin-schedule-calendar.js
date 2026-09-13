import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import idLocale from '@fullcalendar/core/locales/id';

const pad = (number) => String(number).padStart(2, '0');
const dateValue = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
const timeValue = (date) => `${pad(date.getHours())}:${pad(date.getMinutes())}`;
const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

function eventRange(event) {
    const start = new Date(event.start);
    const end = event.end
        ? new Date(event.end)
        : new Date(start.getTime() + (30 * 60 * 1000));

    return { start, end };
}

function eventDisplayRange(event) {
    const range = eventRange(event);
    const startTime = event.extendedProps?.start_time;
    const endTime = event.extendedProps?.end_time;

    if (!startTime || !endTime) return range;

    const [startHour, startMinute] = startTime.split(':').map(Number);
    const [endHour, endMinute] = endTime.split(':').map(Number);
    range.start.setHours(startHour, startMinute, 0, 0);
    range.end = new Date(range.start);
    range.end.setHours(endHour, endMinute, 0, 0);
    if (range.end <= range.start) range.end.setDate(range.end.getDate() + 1);

    return range;
}

function interactionRange(info) {
    const start = new Date(info.event.start);
    const sourceRange = info.oldEvent ? eventDisplayRange(info.oldEvent) : eventRange(info.event);

    // Month view has no time grid. Preserve the original duration/time when
    // an event is moved there instead of submitting 00:00–00:00.
    if (info.view.type === 'dayGridMonth' && info.oldEvent?.start) {
        start.setHours(sourceRange.start.getHours(), sourceRange.start.getMinutes(), 0, 0);
        const duration = sourceRange.end.getTime() - sourceRange.start.getTime();

        return { start, end: new Date(start.getTime() + duration) };
    }

    return eventRange(info.event);
}

function formatRange(range) {
    const date = range.start.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
    const start = range.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const end = range.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

    return `${date}, ${start}–${end}`;
}

document.addEventListener('DOMContentLoaded', () => {
    const element = document.querySelector('[data-admin-schedule-calendar]');
    if (!element) return;

    const dialog = document.getElementById('calendar-change-dialog');
    const form = document.getElementById('calendar-change-form');
    const errorBox = document.getElementById('calendar-change-error');
    const scopeGroup = document.getElementById('calendar-change-scope-group');
    const labFilter = document.getElementById('calendar-lab-filter');
    const submitButton = form.querySelector('button[type="submit"]');
    let pendingChange = null;
    let isSubmitting = false;

    const calendar = new Calendar(element, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        locales: [idLocale],
        locale: 'id',
        timeZone: 'local',
        initialView: 'timeGridWeek',
        firstDay: 1,
        hiddenDays: [0],
        slotMinTime: '07:00:00',
        slotMaxTime: '21:00:00',
        slotDuration: '00:30:00',
        snapDuration: '00:30:00',
        allDaySlot: false,
        nowIndicator: true,
        selectable: true,
        selectMirror: true,
        editable: true,
        eventStartEditable: true,
        eventDurationEditable: true,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay,dayGridMonth',
        },
        buttonText: { today: 'Hari ini', week: 'Pekan', day: 'Hari', month: 'Bulan' },
        events(info, success, failure) {
            const url = new URL(element.dataset.eventsUrl, window.location.origin);
            url.searchParams.set('start', info.startStr);
            url.searchParams.set('end', info.endStr);
            if (labFilter?.value) url.searchParams.set('lab_id', labFilter.value);

            fetch(url, { headers: { Accept: 'application/json' } })
                .then(async (response) => {
                    if (!response.ok) throw new Error(await response.text());
                    return response.json();
                })
                .then(success)
                .catch((error) => {
                    showFeedback('Kalender gagal dimuat. Periksa koneksi lalu coba lagi.', 'error');
                    failure(error);
                });
        },
        selectAllow(info) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return info.start >= today
                && info.start.getDay() !== 0;
        },
        select(info) {
            if (info.view.type === 'dayGridMonth') {
                calendar.changeView('timeGridDay', info.start);
                announce('Pilih rentang jam pada tampilan Hari untuk membuat jadwal.');
                return;
            }

            const url = new URL(element.dataset.createUrl, window.location.origin);
            url.searchParams.set('start_date', dateValue(info.start));
            url.searchParams.set('end_date', dateValue(info.start));
            url.searchParams.set('start_time', timeValue(info.start));
            url.searchParams.set('end_time', timeValue(info.end));
            url.searchParams.set('day', info.start.toLocaleDateString('id-ID', { weekday: 'long' }));
            url.searchParams.set('type', 'perkuliahan_tidak_tetap');
            if (labFilter?.value) url.searchParams.set('lab_id', labFilter.value);
            window.location.assign(url.toString());
        },
        eventDrop(info) {
            openChangeDialog(info, 'move');
        },
        eventResize(info) {
            openChangeDialog(info, 'resize');
        },
        eventClick(info) {
            if (info.jsEvent.defaultPrevented) return;

            const props = info.event.extendedProps;
            const range = eventDisplayRange(info.event);
            const url = new URL(
                element.dataset.editUrl.replace('__ID__', props.schedule_id),
                window.location.origin
            );

            // Carry the clicked occurrence into the edit flow. Without this,
            // editing a recurring event defaults to the entire series.
            if (props.is_recurring && props.occurrence_date) {
                url.searchParams.set('scope', 'single');
                url.searchParams.set('occurrence_date', props.occurrence_date);
                url.searchParams.set('target_date', dateValue(range.start));
            }
            url.searchParams.set('lab_id', props.lab_id);
            url.searchParams.set('start_time', timeValue(range.start));
            url.searchParams.set('end_time', timeValue(range.end));
            window.location.assign(url.toString());
        },
        eventDidMount(info) {
            info.el.tabIndex = 0;
            info.el.setAttribute('role', 'button');
            info.el.setAttribute('aria-label', `${info.event.title}, ${formatRange(eventDisplayRange(info.event))}. Tekan Enter untuk mengedit.`);
            info.el.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    info.el.click();
                }
            });
        },
    });

    function openChangeDialog(info, action) {
        const props = info.event.extendedProps;
        if (props.is_past) {
            info.revert();
            showFeedback('Pertemuan yang sudah terlaksana tidak dapat diubah.', 'error');
            return;
        }

        pendingChange = { info, action, range: interactionRange(info) };
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
        form.reset();
        document.getElementById('calendar-change-summary').textContent =
            `${info.event.title}: ${formatRange(pendingChange.range)}`;
        document.getElementById('calendar-change-lab').value = String(props.lab_id);
        scopeGroup.classList.toggle('hidden', !props.is_recurring);
        document.querySelector('input[name="calendar_change_scope"][value="single"]').checked = true;
        submitButton.disabled = false;
        submitButton.textContent = 'Simpan perubahan';
        dialog.showModal();
        document.getElementById('calendar-change-reason').focus();
    }

    async function submitChange(event) {
        event.preventDefault();
        if (!pendingChange || isSubmitting) return;

        const { info, action, range } = pendingChange;
        const props = info.event.extendedProps;
        const scope = props.is_recurring
            ? form.querySelector('input[name="calendar_change_scope"]:checked').value
            : 'single';
        const reason = document.getElementById('calendar-change-reason').value.trim();
        const labId = document.getElementById('calendar-change-lab').value;

        if (!reason) {
            errorBox.textContent = 'Alasan perubahan wajib diisi.';
            errorBox.classList.remove('hidden');
            return;
        }

        isSubmitting = true;
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitButton.textContent = 'Menyimpan...';

        const recurrenceDays = Array.isArray(props.recurrence_days)
            ? [...props.recurrence_days]
            : null;
        if (scope === 'future' && action === 'move' && recurrenceDays?.length) {
            const originalDay = dayNames[new Date(`${props.occurrence_date}T00:00:00`).getDay()];
            const targetDay = dayNames[range.start.getDay()];
            if (originalDay && targetDay && originalDay !== targetDay) {
                const shiftedDays = recurrenceDays.map((day) => day === originalDay ? targetDay : day);
                recurrenceDays.splice(0, recurrenceDays.length, ...new Set(shiftedDays));
            }
        }

        const payload = {
            action,
            scope,
            occurrence_date: props.occurrence_date,
            target_date: dateValue(range.start),
            lab_id: labId,
            start_time: timeValue(range.start),
            end_time: timeValue(range.end),
            reason,
        };
        if (scope === 'future' && recurrenceDays?.length) {
            payload.recurrence_days = recurrenceDays;
        }

        try {
            const response = await fetch(element.dataset.changeUrl.replace('__ID__', props.schedule_id), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const messages = data.errors ? Object.values(data.errors).flat() : [data.message || 'Perubahan gagal disimpan.'];
                throw new Error(messages.join(' '));
            }

            pendingChange = null;
            dialog.close();
            calendar.refetchEvents();
            announce(data.message);
            showFeedback(data.message, 'success');
        } catch (error) {
            info.revert();
            pendingChange = null;
            dialog.close();
            const message = error.message || 'Perubahan gagal disimpan. Coba lagi.';
            announce(message);
            showFeedback(message, 'error');
        } finally {
            isSubmitting = false;
            submitButton.disabled = false;
            submitButton.removeAttribute('aria-busy');
            submitButton.textContent = 'Simpan perubahan';
        }
    }

    function cancelChange() {
        pendingChange?.info.revert();
        pendingChange = null;
        dialog.close();
    }

    function announce(message) {
        const liveRegion = document.getElementById('calendar-live-region');
        if (!liveRegion) return;
        liveRegion.textContent = '';
        window.setTimeout(() => { liveRegion.textContent = message; }, 50);
    }

    function showFeedback(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        }
    }

    form.addEventListener('submit', submitChange);
    document.getElementById('calendar-change-cancel').addEventListener('click', cancelChange);
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        cancelChange();
    });
    labFilter?.addEventListener('change', () => calendar.refetchEvents());
    window.addEventListener('schedule-calendar-visible', () => {
        calendar.render();
        calendar.updateSize();
    });
    calendar.render();
});
