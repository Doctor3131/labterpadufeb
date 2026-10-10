<?php

namespace App\Services;

use App\Models\RefinitivAttendanceEvent;
use App\Models\RefinitivRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefinitivAttendanceService
{
    public function changeStatus(RefinitivRequest $request, string $newStatus, int $actorId, ?string $note = null): bool
    {
        return DB::transaction(function () use ($request, $newStatus, $actorId, $note): bool {
            $lockedRequest = RefinitivRequest::query()->lockForUpdate()->findOrFail($request->getKey());
            $previousStatus = $lockedRequest->attendance_status;

            if ($previousStatus === $newStatus) {
                return false;
            }

            $recordedAt = now();
            $isPending = $newStatus === 'pending';

            $lockedRequest->forceFill([
                'attendance_status' => $newStatus,
                'attendance_marked_at' => $isPending ? null : $recordedAt,
                'handled_by' => $isPending ? null : $actorId,
            ])->save();

            RefinitivAttendanceEvent::create([
                'refinitiv_request_id' => $lockedRequest->getKey(),
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
                'changed_by' => $actorId,
                'note' => $this->normalizeNote($note),
                'recorded_at' => $recordedAt,
            ]);

            return true;
        });
    }

    /** @param list<int> $requestIds */
    public function markManyPresent(array $requestIds, int $actorId, ?string $note = null): int
    {
        return DB::transaction(function () use ($requestIds, $actorId, $note): int {
            $requests = RefinitivRequest::query()
                ->whereIn('id', $requestIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($requests->count() !== count(array_unique($requestIds))) {
                throw ValidationException::withMessages([
                    'ids' => 'Sebagian permohonan tidak lagi tersedia. Muat ulang daftar lalu coba lagi.',
                ]);
            }

            if ($requests->contains(fn (RefinitivRequest $request): bool => $request->attendance_status !== 'pending')) {
                throw ValidationException::withMessages([
                    'ids' => 'Aksi massal hanya berlaku untuk permohonan yang masih Menunggu. Muat ulang daftar untuk melihat status terbaru.',
                ]);
            }

            $recordedAt = now();
            $normalizedNote = $this->normalizeNote($note);

            foreach ($requests as $request) {
                $request->forceFill([
                    'attendance_status' => 'hadir',
                    'attendance_marked_at' => $recordedAt,
                    'handled_by' => $actorId,
                ])->save();

                RefinitivAttendanceEvent::create([
                    'refinitiv_request_id' => $request->getKey(),
                    'from_status' => 'pending',
                    'to_status' => 'hadir',
                    'changed_by' => $actorId,
                    'note' => $normalizedNote,
                    'recorded_at' => $recordedAt,
                ]);
            }

            return $requests->count();
        });
    }

    private function normalizeNote(?string $note): ?string
    {
        $note = trim((string) $note);

        return $note === '' ? null : $note;
    }
}
