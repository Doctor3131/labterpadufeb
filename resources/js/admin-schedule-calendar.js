import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import idLocale from '@fullcalendar/core/locales/id';

const pad = (number) => String(number).padStart(2, '0');
const dateValue = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
const timeValue = (date) => `${pad(date.getHours())}:${pad(date.getMinutes())}`;

document.addEventListener('DOMContentLoaded', () => {
    const element = document.querySelector('[data-admin-schedule-calendar]');
    if (!element) return;

    const dialog = document.getElementById('calendar-change-dialog');
    const form = document.getElementById('calendar-change-form');
    const errorBox = document.getElementById('calendar-change-error');
    const scopeGroup = document.getElementById('calendar-change-scope-group');
    const labFilter = document.getElementById('calendar-lab-filter');
    let pendingChange = null;

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
                .catch(failure);
        },
        selectAllow(info) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return info.start >= today && info.start.getDay() !== 0;
        },
        select(info) {
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
            const scheduleId = info.event.extendedProps.schedule_id;
            window.location.assign(element.dataset.editUrl.replace('__ID__', scheduleId));
        },
        eventDidMount(info) {
            info.el.tabIndex = 0;
            info.el.setAttribute('role', 'button');
            info.el.setAttribute('aria-label', `${info.event.title}, ${info.event.start?.toLocaleString('id-ID')}. Tekan Enter untuk mengedit.`);
            info.el.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    const scheduleId = info.event.extendedProps.schedule_id;
                    window.location.assign(element.dataset.editUrl.replace('__ID__', scheduleId));
                }
            });
        },
    });

    function openChangeDialog(info, action) {
        const props = info.event.extendedProps;
        if (props.is_past) {
            info.revert();
            return;
        }

        pendingChange = { info, action };
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
        form.reset();
        document.getElementById('calendar-change-summary').textContent =
            `${info.event.title}: ${info.event.start.toLocaleString('id-ID')}–${info.event.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
        document.getElementById('calendar-change-lab').value = String(props.lab_id);
        scopeGroup.classList.toggle('hidden', !props.is_recurring);
        document.querySelector('input[name="calendar_change_scope"][value="single"]').checked = true;
        dialog.showModal();
        document.getElementById('calendar-change-reason').focus();
    }

    async function submitChange(event) {
        event.preventDefault();
        if (!pendingChange) return;

        const { info, action } = pendingChange;
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

        const payload = {
            action,
            scope,
            occurrence_date: props.occurrence_date,
            target_date: dateValue(info.event.start),
            lab_id: labId,
            start_time: timeValue(info.event.start),
            end_time: timeValue(info.event.end),
            reason,
        };

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

            const data = await response.json();
            if (!response.ok) {
                const messages = data.errors ? Object.values(data.errors).flat() : [data.message || 'Perubahan gagal disimpan.'];
                throw new Error(messages.join(' '));
            }

            pendingChange = null;
            dialog.close();
            calendar.refetchEvents();
            announce(data.message);
        } catch (error) {
            errorBox.textContent = error.message;
            errorBox.classList.remove('hidden');
        }
    }

    function cancelChange() {
        pendingChange?.info.revert();
        pendingChange = null;
        dialog.close();
    }

    function announce(message) {
        const liveRegion = document.getElementById('calendar-live-region');
        liveRegion.textContent = '';
        window.setTimeout(() => { liveRegion.textContent = message; }, 50);
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
