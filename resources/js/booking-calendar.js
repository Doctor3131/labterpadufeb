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

    const labPicker = document.getElementById('booking-calendar-lab');
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
            return Boolean(labPicker.value) && info.start >= today && info.start.getDay() !== 0;
        },
        select(info) {
            if (!labPicker.value) return;

            setField('booking_date', localDate(info.start));
            window.bookingCalendarPreferredLabId = labPicker.value;
            setTime('start', localTime(info.start), false);
            setTime('end', localTime(info.end), false);
            document.getElementById('booking_date').dispatchEvent(new Event('change', { bubbles: true }));
            status.textContent = `Slot ${info.start.toLocaleString('id-ID')} hingga ${info.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} dipilih. Lengkapi jumlah peserta, lalu pilih lab.`;
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
        document.getElementById(`${prefix}_hour`).value = hour;
        document.getElementById(`${prefix}_minute`).value = minute;
        const hidden = document.getElementById(`${prefix}_time`);
        hidden.value = value;
        if (dispatch) {
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    labPicker.addEventListener('change', () => {
        status.textContent = labPicker.value
            ? 'Klik atau tarik pada area kosong untuk memilih tanggal dan waktu.'
            : 'Pilih laboratorium untuk melihat ketersediaan.';
        status.className = 'field-help';
        calendar.refetchEvents();
    });

    window.addEventListener('booking-step-3-visible', () => calendar.updateSize());
    calendar.render();
});
