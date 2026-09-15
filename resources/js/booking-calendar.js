import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import idLocale from '@fullcalendar/core/locales/id';

const pad = (number) => String(number).padStart(2, '0');
const localDate = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
const localTime = (date) => `${pad(date.getHours())}:${pad(date.getMinutes())}`;

document.addEventListener('DOMContentLoaded', () => {
    const element = document.querySelector('[data-booking-calendar]');
    if (!element) return;

    // The booking form has one canonical lab field. The calendar reads from
    // and updates the same selection so the user never has to choose a lab twice.
    const labPicker = document.getElementById('labSelect');
    const status = document.getElementById('booking-calendar-status');

    const calendar = new Calendar(element, {
        plugins: [timeGridPlugin, interactionPlugin],
        locales: [idLocale],
        locale: 'id',
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
        selectOverlap: false,
        selectMirror: true,
        height: 540,
        contentHeight: 480,
        expandRows: false,
        scrollTime: '08:00:00',
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        dayHeaderFormat: { weekday: 'short', day: 'numeric', month: 'numeric' },
        headerToolbar: { left: 'prev,next', center: 'title', right: 'today' },
        buttonText: { today: 'Hari ini' },
        events(info, success, failure) {
            if (!labPicker.value) {
                success([]);
                return;
            }

            const url = new URL(element.dataset.availabilityUrl, window.location.origin);
            url.searchParams.set('start', info.startStr);
            url.searchParams.set('end', info.endStr);
            url.searchParams.set('lab_id', labPicker.value);
            fetch(url, { headers: { Accept: 'application/json' } })
                .then(async (response) => {
                    if (!response.ok) throw new Error(await response.text());
                    return response.json();
                })
                .then(success)
                .catch((error) => {
                    status.textContent = 'Kalendar belum dapat dimuat. Coba pilih lab atau rentang tanggal lain.';
                    status.className = 'text-red-700 text-sm';
                    failure(error);
                });
        },
        selectAllow(info) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return Boolean(labPicker.value) && !labPicker.disabled && info.start >= today && info.start.getDay() !== 0;
        },
        select(info) {
            if (!labPicker.value) return;

            setField('booking_date', localDate(info.start));
            setTime('start', localTime(info.start), false);
            setTime('end', localTime(info.end), false);
            // Trigger the form refresh only after all three canonical values
            // are ready. This avoids a partial availability request with the
            // previous time range and keeps the selected time in the payload.
            document.getElementById('end_time').dispatchEvent(new Event('change', { bubbles: true }));
            document.getElementById('start_time').value = localTime(info.start);
            document.getElementById('end_time').value = localTime(info.end);
            document.dispatchEvent(new CustomEvent('booking-calendar:time-selected', {
                detail: {
                    start: localTime(info.start),
                    end: localTime(info.end),
                },
            }));
            status.textContent = `Slot ${info.start.toLocaleString('id-ID')} hingga ${info.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} dipilih. Lengkapi jumlah peserta untuk memeriksa kembali ketersediaannya.`;
            status.className = 'text-amber-800 text-sm font-medium';
            document.getElementById('participant_count').focus();
        },
    });

    function setField(id, value) {
        const field = document.getElementById(id);
        field.value = value;
    }

    function setTime(prefix, value, dispatch = true) {
        const [hour, minute] = value.split(':');
        const hourSelect = document.getElementById(`${prefix}_hour`);
        const minuteSelect = document.getElementById(`${prefix}_minute`);
        hourSelect.value = hour;
        minuteSelect.value = minute;
        const hidden = document.getElementById(`${prefix}_time`);

        // Programmatic value changes do not update the custom dropdown trigger.
        // Use a presentation-only event so the normal time listener does not
        // start a partial availability request for each half of the time.
        const calendarSyncEvent = new Event('custom-select:sync', { bubbles: true });
        hourSelect.dispatchEvent(calendarSyncEvent);
        minuteSelect.dispatchEvent(calendarSyncEvent);

        // Re-assert the submitted value after the visual controls have been
        // notified. This keeps the canonical field correct regardless of
        // listener order or browser event timing.
        hidden.value = value;
        hidden.defaultValue = value;
        hidden.setAttribute('value', value);
        if (dispatch) {
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function updateCalendarStatus() {
        status.textContent = labPicker.value
            ? 'Klik atau tarik pada area kosong untuk mengisi tanggal dan jam.'
            : 'Pilih laboratorium untuk melihat ketersediaan.';
        status.className = 'field-help';
    }

    labPicker.addEventListener('change', () => {
        updateCalendarStatus();
        calendar.refetchEvents();
    });

    window.addEventListener('booking-step-3-visible', () => calendar.updateSize());
    updateCalendarStatus();
    calendar.render();
});
