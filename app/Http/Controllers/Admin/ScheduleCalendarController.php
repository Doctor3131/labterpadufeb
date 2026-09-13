<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\ScheduleCalendarService;
use App\Services\ScheduleChangeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScheduleCalendarController extends Controller
{
    public function events(Request $request, ScheduleCalendarService $calendar): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'lab_id' => ['nullable', 'integer', 'exists:labs,id'],
        ]);

        $start = Carbon::parse($validated['start'])->startOfDay();
        // FullCalendar sends an exclusive end date.
        $end = Carbon::parse($validated['end'])->startOfDay()->subDay();
        if ($start->diffInDays($end) > 62) {
            throw ValidationException::withMessages(['end' => 'Rentang kalender maksimal 63 hari.']);
        }

        $labIds = isset($validated['lab_id']) ? [(int) $validated['lab_id']] : [];
        $today = now('Asia/Jakarta')->startOfDay();

        $events = $calendar->events($start, $end, $labIds)->map(function (array $event) use ($today) {
            $editable = Carbon::parse($event['date'])->gte($today);

            return [
                'id' => $event['id'],
                'title' => trim(($event['course'] ?: 'Jadwal').' · '.($event['lab'] ?: 'Lab')),
                'start' => $event['date'].'T'.$event['start_time'],
                'end' => $event['date'].'T'.$event['end_time'],
                'editable' => $editable,
                'startEditable' => $editable,
                'durationEditable' => $editable,
                'backgroundColor' => $this->eventColor($event['booking_type']),
                'borderColor' => $this->eventColor($event['booking_type']),
                'extendedProps' => $event,
            ];
        });

        return response()->json($events);
    }

    public function change(
        Request $request,
        Schedule $schedule,
        ScheduleChangeService $changes
    ): JsonResponse {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['move', 'resize', 'cancel', 'end_date'])],
            'scope' => ['required', Rule::in(['single', 'future'])],
            'occurrence_date' => ['required_unless:action,end_date', 'nullable', 'date'],
            'target_date' => ['required_if:action,move,resize', 'nullable', 'date'],
            'lab_id' => ['required_if:action,move,resize', 'nullable', 'integer', 'exists:labs,id'],
            'start_time' => ['required_if:action,move,resize', 'nullable', 'date_format:H:i'],
            'end_time' => ['required_if:action,move,resize', 'nullable', 'date_format:H:i', 'after:start_time'],
            'recurrence_days' => ['nullable', 'array', 'min:1'],
            'recurrence_days.*' => ['in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'end_date' => ['required_if:action,end_date', 'nullable', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $userId = $request->user()?->id;
        $action = $validated['action'];

        if ($action === 'end_date') {
            $updated = $changes->changeEndDate($schedule, Carbon::parse($validated['end_date']), $validated['reason'], $userId);

            return response()->json(['message' => 'Tanggal selesai berhasil diperbarui.', 'schedule_id' => $updated->id]);
        }

        $originalDate = Carbon::parse($validated['occurrence_date']);

        if ($action === 'cancel') {
            if ($validated['scope'] === 'future') {
                $changes->cancelFuture($schedule, $originalDate, $validated['reason'], $userId);
            } else {
                $changes->cancelOccurrence($schedule, $originalDate, $validated['reason'], $userId);
            }

            return response()->json(['message' => 'Jadwal berhasil dibatalkan tanpa menghapus histori.']);
        }

        $targetDate = Carbon::parse($validated['target_date']);
        if ($validated['scope'] === 'future') {
            $updated = $changes->changeFuture(
                $schedule,
                $originalDate,
                $targetDate,
                [
                    'lab_id' => (int) $validated['lab_id'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'recurrence_days' => $validated['recurrence_days'] ?? null,
                ],
                $validated['reason'],
                $userId
            );

            return response()->json(['message' => 'Rangkaian masa depan berhasil diperbarui.', 'schedule_id' => $updated->id]);
        }

        $changes->moveOccurrence(
            $schedule,
            $originalDate,
            $targetDate,
            (int) $validated['lab_id'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['reason'],
            $userId
        );

        return response()->json(['message' => 'Pertemuan berhasil diperbarui.']);
    }

    private function eventColor(string $type): string
    {
        return match ($type) {
            'perkuliahan_tetap' => '#d97706',
            'perkuliahan_tidak_tetap' => '#4f46e5',
            'non_perkuliahan' => '#059669',
            default => '#475569',
        };
    }
}
