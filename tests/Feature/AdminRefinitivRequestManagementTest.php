<?php

namespace Tests\Feature;

use App\Models\RefinitivRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
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
            ->assertSee('data-refinitiv-sort-trigger', false)
            ->assertSee('data-refinitiv-admin', false)
            ->assertSee('data-refinitiv-status="pending"', false)
            ->assertSee('id="refinitiv-results-region"', false)
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

        $this->assertSame(1, $response->json('total'));
        $this->assertStringContainsString('Nadia AJAX', $response->json('html'));
        $this->assertStringNotContainsString('Bima AJAX', $response->json('html'));
        $this->assertStringNotContainsString('refinitiv-status-tabs', $response->json('html'));
        $this->assertStringNotContainsString('<html', $response->json('html'));
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

        for ($index = 1; $index <= 16; $index++) {
            $this->createRequest([
                'name' => sprintf('Pemohon Batch %02d', $index),
                'attendance_status' => 'pending',
                'created_at' => now()->subDays($index),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.refinitiv.index', [
            'status' => 'pending',
            'q' => 'Batch',
            'sort' => 'name_asc',
        ]));

        $response->assertOk()
            ->assertSee(e(route('admin.refinitiv.index', [
                'status' => 'hadir',
                'q' => 'Batch',
                'sort' => 'name_asc',
            ])), false)
            ->assertSee(e(route('admin.refinitiv.show', [
                'request' => RefinitivRequest::query()->where('name', 'Pemohon Batch 01')->firstOrFail(),
                'status' => 'pending',
                'q' => 'Batch',
                'sort' => 'name_asc',
            ])), false)
            ->assertViewHas('requests', function (LengthAwarePaginator $requests): bool {
                $nextPageUrl = $requests->nextPageUrl();

                return $nextPageUrl !== null
                    && str_contains($nextPageUrl, 'status=pending')
                    && str_contains($nextPageUrl, 'q=Batch')
                    && str_contains($nextPageUrl, 'sort=name_asc');
            });
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
