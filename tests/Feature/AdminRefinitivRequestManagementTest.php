<?php

namespace Tests\Feature;

use App\Models\RefinitivRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminRefinitivRequestManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_refinitiv_list_uses_an_accessible_table_and_automatic_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createRequest(['name' => 'Nadia Refinitiv']);

        $this->actingAs($admin)
            ->get(route('admin.refinitiv.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('<table', false)
            ->assertSee('Daftar permohonan data Refinitiv')
            ->assertSee('data-refinitiv-custom-trigger', false)
            ->assertSee('refinitiv-period', false)
            ->assertSee('data-refinitiv-bulk-toolbar', false)
            ->assertSee('refinitiv-attendance-confirm', false)
            ->assertSee('data-refinitiv-admin', false)
            ->assertSee('data-refinitiv-calendar-region', false)
            ->assertSee('minmax(0,3fr)', false)
            ->assertSee('data-refinitiv-status="pending"', false)
            ->assertSee('id="refinitiv-results-region"', false)
            ->assertSee('data-refinitiv-clear-date', false)
            ->assertSee('Kalender peminjaman Refinitiv')
            ->assertSee('memfilter daftar di samping')
            ->assertSee('data-refinitiv-calendar-session="sesi_1"', false)
            ->assertSee('Filter otomatis')
            ->assertSee('Nadia Refinitiv')
            ->assertSee('Hadir')
            ->assertDontSee('Terapkan');
    }

    public function test_admin_refinitiv_ajax_filter_returns_only_the_results_fragment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createRequest(['name' => 'Nadia AJAX', 'attendance_status' => 'hadir']);
        $this->createRequest(['name' => 'Bima AJAX', 'attendance_status' => 'pending']);

        $response = $this->actingAs($admin)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->get(route('admin.refinitiv.index', [
                'status' => 'hadir',
                'q' => 'Nadia',
                'sort' => 'name_asc',
            ]));

        $response->assertOk()->assertJsonStructure(['html', 'total']);
        $this->assertArrayNotHasKey('calendarHtml', $response->json());

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString('Nadia AJAX', $response->json('html'));
        $this->assertStringNotContainsString('Bima AJAX', $response->json('html'));
        $this->assertStringNotContainsString('refinitiv-status-tabs', $response->json('html'));
        $this->assertStringNotContainsString('<html', $response->json('html'));
    }

    public function test_calendar_month_navigation_returns_only_the_calendar_fragment_over_ajax(): void
    {
        Carbon::setTestNow('2026-09-24 10:00:00');
        try {
            $admin = User::factory()->create(['role' => 'admin']);
            $this->createRequest(['name' => 'Pemohon Oktober', 'usage_date' => '2026-10-08']);

            $response = $this->actingAs($admin)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest',
                ])
                ->get(route('admin.refinitiv.index', [
                    'status' => 'all',
                    'month' => '2026-10',
                    'calendar_fragment' => 1,
                ]));

            $response->assertOk()->assertJsonStructure(['html', 'total', 'counts', 'calendarHtml']);
            $this->assertStringContainsString('Oktober 2026', $response->json('calendarHtml'));
            $this->assertStringContainsString('2026-10-08', $response->json('calendarHtml'));
            $this->assertStringContainsString('data-refinitiv-calendar-nav', $response->json('calendarHtml'));
            $this->assertStringNotContainsString('<html', $response->json('calendarHtml'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_admin_refinitiv_calendar_shows_a_clear_action_for_an_active_date_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.refinitiv.index', [
                'status' => 'all',
                'q' => 'Nadia',
                'date' => '2026-09-25',
            ]))
            ->assertOk()
            ->assertSee('data-refinitiv-clear-date', false)
            ->assertSee('Hapus filter tanggal')
            ->assertSee('Daftar di samping difilter berdasarkan tanggal ini.')
            ->assertSee('value="Nadia"', false)
            ->assertSee('value="2026-09-25"', false);
    }

    public function test_admin_refinitiv_detail_keeps_actions_and_previews_uploaded_documents(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = $this->createRequest([
            'name' => 'Nadia Detail',
            'ktm_file' => 'refinitiv/ktm-nadia.jpg',
            'statement_file' => 'refinitiv/pernyataan-nadia.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.refinitiv.show', [
                'request' => $request,
                'status' => 'pending',
                'q' => 'Nadia',
                'sort' => 'name_asc',
            ]))
            ->assertOk()
            ->assertSee('refinitiv-detail-page')
            ->assertSee('Data pemohon')
            ->assertSee('Keperluan dan jadwal')
            ->assertSee('Dokumen pemohon')
            ->assertSee('data-preview-type="image"', false)
            ->assertSee('data-preview-type="pdf"', false)
            ->assertSee('Tandai hadir')
            ->assertSee('Tandai tidak hadir')
            ->assertSee('data-refinitiv-confirm', false)
            ->assertSee('Riwayat status kehadiran')
            ->assertSee('@view-transition')
            ->assertDontSee('data-motion-item', false);
    }

    public function test_admin_can_combine_attendance_status_and_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matchingPending = $this->createRequest([
            'name' => 'Nadia Searchable',
            'nim_nip' => 'NIM-NADIA',
            'whatsapp' => 'PHONE-NADIA',
            'attendance_status' => 'pending',
        ]);
        $matchingPresent = $this->createRequest([
            'name' => 'Nadia Searchable Hadir',
            'nim_nip' => 'NIM-NADIA-HADIR',
            'whatsapp' => 'PHONE-NADIA-HADIR',
            'attendance_status' => 'hadir',
        ]);
        $unmatched = $this->createRequest([
            'name' => 'Bima Lainnya',
            'nim_nip' => 'NIM-BIMA',
            'whatsapp' => 'PHONE-BIMA',
            'attendance_status' => 'hadir',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.refinitiv.index', [
            'status' => 'hadir',
            'q' => 'Nadia Searchable',
            'sort' => 'name_asc',
        ]));

        $response->assertOk()
            ->assertViewHas('status', 'hadir')
            ->assertViewHas('search', 'Nadia Searchable')
            ->assertViewHas('sort', 'name_asc')
            ->assertViewHas('requests', function (LengthAwarePaginator $requests) use ($matchingPresent, $matchingPending, $unmatched): bool {
                $ids = $requests->getCollection()->modelKeys();

                return $ids === [$matchingPresent->id]
                    && ! in_array($matchingPending->id, $ids, true)
                    && ! in_array($unmatched->id, $ids, true);
            });
    }

    public function test_search_matches_name_nim_whatsapp_and_exact_numeric_request_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $first = $this->createRequest([
            'name' => 'Ratih Prameswari',
            'nim_nip' => 'NIM-RA-ALPHA',
            'whatsapp' => 'PHONE-RA-ALPHA',
        ]);
        $second = $this->createRequest([
            'name' => 'Bagus Pratama',
            'nim_nip' => 'NIM-BG-BETA',
            'whatsapp' => 'PHONE-BG-BETA',
        ]);

        $searchCases = [
            ['query' => 'Ratih Prameswari', 'expectedId' => $first->id],
            ['query' => 'NIM-RA-ALPHA', 'expectedId' => $first->id],
            ['query' => 'PHONE-RA-ALPHA', 'expectedId' => $first->id],
            ['query' => (string) $second->id, 'expectedId' => $second->id],
        ];

        foreach ($searchCases as $case) {
            $response = $this->actingAs($admin)->get(route('admin.refinitiv.index', [
                'status' => 'all',
                'q' => $case['query'],
                'sort' => 'schedule_asc',
            ]));

            $response->assertOk()->assertViewHas('requests', function (LengthAwarePaginator $requests) use ($case): bool {
                return $requests->getCollection()->modelKeys() === [$case['expectedId']];
            });
        }
    }

    public function test_search_also_matches_request_purpose_variables_program_and_lecturer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matching = $this->createRequest([
            'name' => 'Pemohon Cari Lanjutan',
            'purpose' => 'lainnya',
            'purpose_other' => 'Studi pasar saham',
            'variables' => 'Kode sektor energi dan emiten.',
            'lecturer_name' => 'Dr. Sinta Dosen',
            'study_program' => 'S1- Ekonomi Islam',
            'affiliation' => 'internal_undip',
            'session' => 'sesi_2',
        ]);
        $this->createRequest(['name' => 'Tidak cocok', 'purpose' => 'lomba', 'affiliation' => 'internal_undip', 'session' => 'sesi_2']);

        foreach (['pasar saham', 'emiten', 'Sinta Dosen', 'Ekonomi Islam'] as $term) {
            $this->actingAs($admin)
                ->get(route('admin.refinitiv.index', ['status' => 'all', 'q' => $term]))
                ->assertOk()
                ->assertViewHas('requests', fn (LengthAwarePaginator $requests): bool => $requests->getCollection()->modelKeys() === [$matching->id]);
        }

        $labelMatch = $this->createRequest([
            'name' => 'Pemohon Label',
            'purpose' => 'skripsi',
            'affiliation' => 'internal_feb',
            'session' => 'sesi_1',
        ]);

        foreach (['Skripsi', 'Internal FEB Undip', '08.00 - 10.00 WIB'] as $term) {
            $response = $this->actingAs($admin)
                ->get(route('admin.refinitiv.index', ['status' => 'all', 'q' => $term]))
                ->assertOk();

            $this->assertSame([$labelMatch->id], $response->viewData('requests')->getCollection()->modelKeys(), "Search failed for display label: {$term}");
        }
    }

    public function test_period_filters_return_expected_usage_dates(): void
    {
        Carbon::setTestNow('2026-09-23 10:00:00');
        try {
            $admin = User::factory()->create(['role' => 'admin']);
            $today = $this->createRequest(['name' => 'Hari ini', 'usage_date' => '2026-09-23']);
            $week = $this->createRequest(['name' => 'Minggu ini', 'usage_date' => '2026-09-29']);
            $outsideWeek = $this->createRequest(['name' => 'Lebih dari seminggu', 'usage_date' => '2026-09-30']);
            $overdue = $this->createRequest(['name' => 'Sudah lewat', 'usage_date' => '2026-09-22']);
            $this->assertSame('2026-09-29', $week->usage_date->toDateString());

            $cases = [
                'today' => [$today->id],
                'next_7_days' => [$today->id, $week->id],
                'overdue' => [$overdue->id],
            ];

            foreach ($cases as $period => $expectedIds) {
                $response = $this->actingAs($admin)
                    ->get(route('admin.refinitiv.index', ['status' => 'all', 'period' => $period]))
                    ->assertOk()
                    ->assertViewHas('period', $period);

                $requests = $response->viewData('requests');
                $this->assertSame($expectedIds, $requests->getCollection()->modelKeys(), "Unexpected records for period filter: {$period}");
                $this->assertNotContains($outsideWeek->id, $requests->getCollection()->modelKeys());
            }
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_calendar_summarizes_all_month_requests_by_day_and_session_even_when_list_status_is_filtered(): void
    {
        Carbon::setTestNow('2026-09-24 10:00:00');
        try {
            $admin = User::factory()->create(['role' => 'admin']);
            $this->createRequest(['name' => 'Pemohon Jumat Menunggu', 'usage_date' => '2026-09-25', 'session' => 'sesi_1', 'attendance_status' => 'pending']);
            $this->createRequest(['name' => 'Pemohon Jumat Hadir', 'usage_date' => '2026-09-25', 'session' => 'sesi_3', 'attendance_status' => 'hadir']);
            $this->createRequest(['name' => 'Pemohon Kamis', 'usage_date' => '2026-09-24', 'session' => 'sesi_2', 'attendance_status' => 'pending']);
            $this->createRequest(['name' => 'Bulan Depan', 'usage_date' => '2026-10-01', 'session' => 'sesi_1']);

            $response = $this->actingAs($admin)
                ->get(route('admin.refinitiv.index', ['status' => 'pending', 'month' => '2026-09', 'date' => '2026-09-25']))
                ->assertOk()
                ->assertSee('13.30–15.30 WIB')
                ->assertViewHas('calendarSummary', function (array $summary): bool {
                    return $summary['total'] === 3
                        && $summary['active_days'] === 2
                        && $summary['session_totals']['sesi_1'] === 1
                        && $summary['session_totals']['sesi_2'] === 1
                        && $summary['session_totals']['sesi_3'] === 1;
                })
                ->assertViewHas('calendarSelectedDay', fn (array $day): bool => $day['total'] === 2
                    && $day['statuses']['pending'] === 1
                    && $day['statuses']['hadir'] === 1);

            $this->assertSame(1, $response->viewData('requests')->total());
            $this->assertSame('sesi_1', $response->viewData('calendarSummary')['busiest_session']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_calendar_date_filter_overrides_period_and_returns_all_attendance_status_counts_for_that_date(): void
    {
        Carbon::setTestNow('2026-09-24 10:00:00');
        try {
            $admin = User::factory()->create(['role' => 'admin']);
            $this->createRequest(['name' => 'Jumat Menunggu', 'usage_date' => '2026-09-25', 'attendance_status' => 'pending']);
            $this->createRequest(['name' => 'Jumat Hadir', 'usage_date' => '2026-09-25', 'attendance_status' => 'hadir']);
            $this->createRequest(['name' => 'Kamis Menunggu', 'usage_date' => '2026-09-24', 'attendance_status' => 'pending']);

            $response = $this->actingAs($admin)
                ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
                ->get(route('admin.refinitiv.index', [
                    'status' => 'all',
                    'period' => 'today',
                    'month' => '2026-09',
                    'date' => '2026-09-25',
                ]));

            $response->assertOk()->assertJsonStructure(['html', 'total', 'counts' => ['all', 'pending', 'hadir', 'tidak_hadir']]);
            $this->assertSame(2, $response->json('total'));
            $this->assertSame(['all' => 2, 'pending' => 1, 'hadir' => 1, 'tidak_hadir' => 0], $response->json('counts'));
            $this->assertStringContainsString('Jumat Menunggu', $response->json('html'));
            $this->assertStringContainsString('Jumat Hadir', $response->json('html'));
            $this->assertStringNotContainsString('Kamis Menunggu', $response->json('html'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_search_period_and_status_counts_are_reflected_in_ajax_response(): void
    {
        Carbon::setTestNow('2026-09-23 10:00:00');
        try {
            $admin = User::factory()->create(['role' => 'admin']);
            $this->createRequest(['name' => 'Nadia minggu', 'usage_date' => '2026-09-25', 'attendance_status' => 'pending']);
            $this->createRequest(['name' => 'Nadia hadir', 'usage_date' => '2026-09-26', 'attendance_status' => 'hadir']);
            $this->createRequest(['name' => 'Bima minggu', 'usage_date' => '2026-09-27', 'attendance_status' => 'pending']);

            $response = $this->actingAs($admin)
                ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
                ->get(route('admin.refinitiv.index', ['status' => 'pending', 'q' => 'Nadia', 'period' => 'next_7_days']));

            $response->assertOk()->assertJsonStructure(['html', 'total', 'counts' => ['all', 'pending', 'hadir', 'tidak_hadir']]);
            $this->assertSame(1, $response->json('total'));
            $this->assertSame(['all' => 2, 'pending' => 1, 'hadir' => 1, 'tidak_hadir' => 0], $response->json('counts'));
            $this->assertStringContainsString('Nadia minggu', $response->json('html'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_attendance_changes_and_resets_are_recorded_without_losing_previous_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = $this->createRequest();

        $this->actingAs($admin)
            ->from(route('admin.refinitiv.show', $request))
            ->put(route('admin.refinitiv.hadir', $request), ['note' => 'Kehadiran diverifikasi'])
            ->assertRedirect(route('admin.refinitiv.show', $request));

        $this->assertDatabaseHas('refinitiv_attendance_events', [
            'refinitiv_request_id' => $request->id,
            'from_status' => 'pending',
            'to_status' => 'hadir',
            'changed_by' => $admin->id,
            'note' => 'Kehadiran diverifikasi',
        ]);

        $this->put(route('admin.refinitiv.reset', $request), ['note' => 'Perlu jadwalkan ulang'])
            ->assertRedirect(route('admin.refinitiv.show', $request));

        $this->assertDatabaseHas('refinitiv_requests', [
            'id' => $request->id,
            'attendance_status' => 'pending',
            'attendance_marked_at' => null,
            'handled_by' => null,
        ]);
        $this->assertDatabaseCount('refinitiv_attendance_events', 2);
        $this->actingAs($admin)
            ->get(route('admin.refinitiv.show', $request))
            ->assertOk()
            ->assertSee('Riwayat status kehadiran')
            ->assertSee('Kehadiran diverifikasi')
            ->assertSee('Perlu jadwalkan ulang');
    }

    public function test_bulk_attendance_marks_selected_pending_requests_and_logs_each_change(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $first = $this->createRequest(['name' => 'Pemohon Massal A']);
        $second = $this->createRequest(['name' => 'Pemohon Massal B']);

        $this->actingAs($admin)
            ->from(route('admin.refinitiv.index'))
            ->post(route('admin.refinitiv.bulk-hadir'), [
                'ids' => [$first->id, $second->id],
                'note' => 'Hadir pada sesi terjadwal',
            ])
            ->assertRedirect(route('admin.refinitiv.index'))
            ->assertSessionHas('success', '2 permohonan berhasil ditandai Hadir.');

        $this->assertDatabaseCount('refinitiv_attendance_events', 2);
        $this->assertDatabaseHas('refinitiv_requests', ['id' => $first->id, 'attendance_status' => 'hadir', 'handled_by' => $admin->id]);
        $this->assertDatabaseHas('refinitiv_attendance_events', ['refinitiv_request_id' => $second->id, 'to_status' => 'hadir', 'note' => 'Hadir pada sesi terjadwal']);
    }

    public function test_bulk_attendance_is_atomic_and_refuses_a_non_pending_selection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pending = $this->createRequest(['name' => 'Masih Menunggu']);
        $present = $this->createRequest(['name' => 'Sudah Hadir', 'attendance_status' => 'hadir']);

        $this->actingAs($admin)
            ->from(route('admin.refinitiv.index'))
            ->post(route('admin.refinitiv.bulk-hadir'), ['ids' => [$pending->id, $present->id]])
            ->assertRedirect(route('admin.refinitiv.index'))
            ->assertSessionHasErrors('ids');

        $this->assertDatabaseHas('refinitiv_requests', ['id' => $pending->id, 'attendance_status' => 'pending']);
        $this->assertDatabaseCount('refinitiv_attendance_events', 0);
    }

    public function test_supported_status_filters_and_counts_are_available_to_the_view(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createRequest(['attendance_status' => 'pending']);
        $this->createRequest(['attendance_status' => 'pending']);
        $this->createRequest(['attendance_status' => 'hadir']);
        $this->createRequest(['attendance_status' => 'tidak_hadir']);

        foreach (['all', 'pending', 'hadir', 'tidak_hadir'] as $status) {
            $this->actingAs($admin)
                ->get(route('admin.refinitiv.index', ['status' => $status]))
                ->assertOk()
                ->assertViewHas('status', $status);
        }

        $this->actingAs($admin)
            ->get(route('admin.refinitiv.index', ['status' => 'all']))
            ->assertViewHas('counts', function (array $counts): bool {
                $expected = [
                    'all' => 4,
                    'pending' => 2,
                    'hadir' => 1,
                    'tidak_hadir' => 1,
                ];
                $actual = array_intersect_key($counts, $expected);
                ksort($actual);
                ksort($expected);

                return $actual === $expected;
            });
    }

    public function test_default_schedule_sort_and_name_and_recent_sorts_are_applied(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $today = today();
        $laterSchedule = $this->createRequest([
            'name' => 'Zahra',
            'usage_date' => $today->copy()->addDays(20)->toDateString(),
            'created_at' => now()->subDays(2),
        ]);
        $earlierSchedule = $this->createRequest([
            'name' => 'Budi',
            'usage_date' => $today->copy()->addDays(5)->toDateString(),
            'created_at' => now()->subDays(4),
        ]);
        $recentRequest = $this->createRequest([
            'name' => 'Citra',
            'usage_date' => $today->copy()->addDays(12)->toDateString(),
            'created_at' => now()->subDay(),
        ]);
        $todaySchedule = $this->createRequest([
            'name' => 'Nadia',
            'usage_date' => $today->toDateString(),
            'created_at' => now()->subDays(3),
        ]);
        $pastSchedule = $this->createRequest([
            'name' => 'Sari',
            'usage_date' => $today->copy()->subDays(2)->toDateString(),
            'created_at' => now()->subDays(5),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.refinitiv.index', ['status' => 'all']))
            ->assertOk()
            ->assertViewHas('sort', 'schedule_asc')
            ->assertViewHas('requests', fn (LengthAwarePaginator $requests): bool => $requests->getCollection()->modelKeys() === [
                $todaySchedule->id,
                $earlierSchedule->id,
                $recentRequest->id,
                $laterSchedule->id,
                $pastSchedule->id,
            ]);

        $this->actingAs($admin)
            ->get(route('admin.refinitiv.index', ['status' => 'all', 'sort' => 'schedule_desc']))
            ->assertOk()
            ->assertViewHas('sort', 'schedule_desc')
            ->assertViewHas('requests', fn (LengthAwarePaginator $requests): bool => $requests->getCollection()->modelKeys() === [
                $laterSchedule->id,
                $recentRequest->id,
                $earlierSchedule->id,
                $todaySchedule->id,
                $pastSchedule->id,
            ]);

        $this->actingAs($admin)
            ->get(route('admin.refinitiv.index', ['status' => 'all', 'sort' => 'name_asc']))
            ->assertOk()
            ->assertViewHas('requests', fn (LengthAwarePaginator $requests): bool => $requests->getCollection()->modelKeys() === [
                $earlierSchedule->id,
                $recentRequest->id,
                $todaySchedule->id,
                $pastSchedule->id,
                $laterSchedule->id,
            ]);

        $this->actingAs($admin)
            ->get(route('admin.refinitiv.index', ['status' => 'all', 'sort' => 'recent']))
            ->assertOk()
            ->assertViewHas('requests', fn (LengthAwarePaginator $requests): bool => $requests->getCollection()->modelKeys() === [
                $recentRequest->id,
                $laterSchedule->id,
                $todaySchedule->id,
                $earlierSchedule->id,
                $pastSchedule->id,
            ]);
    }

    public function test_state_links_and_pagination_preserve_search_and_sort_parameters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $selectedDate = today()->addDays(2);

        for ($index = 1; $index <= 16; $index++) {
            $this->createRequest([
                'name' => sprintf('Pemohon Batch %02d', $index),
                'attendance_status' => 'pending',
                'usage_date' => $selectedDate->toDateString(),
                'created_at' => now()->subDays($index),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.refinitiv.index', [
            'status' => 'pending',
            'q' => 'Batch',
            'sort' => 'name_asc',
            'period' => 'next_7_days',
            'month' => $selectedDate->format('Y-m'),
            'date' => $selectedDate->toDateString(),
        ]));

        $response->assertOk()
            ->assertSee(e(route('admin.refinitiv.index', [
                'status' => 'hadir',
                'q' => 'Batch',
                'sort' => 'name_asc',
                'period' => 'next_7_days',
                'month' => $selectedDate->format('Y-m'),
                'date' => $selectedDate->toDateString(),
            ])), false)
            ->assertSee(e(route('admin.refinitiv.show', [
                'request' => RefinitivRequest::query()->where('name', 'Pemohon Batch 01')->firstOrFail(),
                'status' => 'pending',
                'q' => 'Batch',
                'sort' => 'name_asc',
                'period' => 'next_7_days',
                'month' => $selectedDate->format('Y-m'),
                'date' => $selectedDate->toDateString(),
            ])), false)
            ->assertViewHas('requests', function (LengthAwarePaginator $requests) use ($selectedDate): bool {
                $nextPageUrl = $requests->nextPageUrl();

                return $nextPageUrl !== null
                    && str_contains($nextPageUrl, 'status=pending')
                    && str_contains($nextPageUrl, 'q=Batch')
                    && str_contains($nextPageUrl, 'sort=name_asc')
                    && str_contains($nextPageUrl, 'period=next_7_days')
                    && str_contains($nextPageUrl, 'month='.$selectedDate->format('Y-m'))
                    && str_contains($nextPageUrl, 'date='.$selectedDate->toDateString());
            });
    }

    public function test_refinitiv_detail_returns_to_the_same_filtered_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $selectedDate = today()->addDays(2);
        $requests = collect();

        for ($index = 1; $index <= 16; $index++) {
            $requests->push($this->createRequest([
                'name' => sprintf('Pemohon Halaman %02d', $index),
                'attendance_status' => 'pending',
                'usage_date' => $selectedDate->toDateString(),
            ]));
        }

        $filters = [
            'status' => 'pending',
            'q' => 'Halaman',
            'sort' => 'name_asc',
            'period' => 'next_7_days',
            'month' => $selectedDate->format('Y-m'),
            'date' => $selectedDate->toDateString(),
            'page' => 2,
        ];
        $detail = $requests->last();

        $this->actingAs($admin)
            ->get(route('admin.refinitiv.index', $filters))
            ->assertOk()
            ->assertSee(e(route('admin.refinitiv.show', array_merge($filters, ['request' => $detail]))), false);

        $this->get(route('admin.refinitiv.show', array_merge($filters, ['request' => $detail])))
            ->assertOk()
            ->assertSee(e(route('admin.refinitiv.index', $filters)), false);
    }

    private function createRequest(array $overrides = []): RefinitivRequest
    {
        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at']);

        $request = RefinitivRequest::create(array_merge([
            'token' => bin2hex(random_bytes(32)),
            'name' => 'Pemohon Uji',
            'nim_nip' => 'NIM-UJI',
            'whatsapp' => 'PHONE-UJI',
            'affiliation' => 'internal_feb',
            'applicant_type' => 'mahasiswa',
            'study_program' => 'S1- Manajemen',
            'purpose' => 'skripsi',
            'usage_date' => '2026-10-05',
            'session' => 'sesi_1',
            'variables' => 'Data penelitian untuk pengujian.',
            'statement_file' => 'refinitiv/statement/test.pdf',
            'attendance_status' => 'pending',
        ], $overrides));

        if ($createdAt !== null) {
            $request->forceFill(['created_at' => $createdAt])->save();
        }

        return $request;
    }
}
