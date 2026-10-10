<?php

namespace Tests\Unit;

use App\Enums\CategoryEnum;
use App\Enums\ConditionEnum;
use App\Enums\TrackingModeEnum;
use App\Helpers\DayHelper;
use App\Models\AssetBorrowing;
use App\Models\InventoryBalance;
use App\Models\RefinitivRequest;
use Tests\TestCase;

class DomainPolicyTest extends TestCase
{
    public function test_asset_conditions_expose_stable_labels_colors_and_usability(): void
    {
        $expected = [
            ConditionEnum::BAIK->value => ['Baik', 'bg-green-100 text-green-800', true],
            ConditionEnum::RUSAK->value => ['Rusak', 'bg-red-100 text-red-800', false],
            ConditionEnum::HILANG->value => ['Hilang', 'bg-gray-100 text-gray-800', false],
            ConditionEnum::MAINTENANCE->value => ['Maintenance', 'bg-yellow-100 text-yellow-800', false],
        ];

        foreach (ConditionEnum::cases() as $condition) {
            [$label, $color, $usable] = $expected[$condition->value];
            $this->assertSame($label, $condition->label());
            $this->assertSame($color, $condition->colorClass());
            $this->assertSame($usable, $condition->isUsable());
        }
    }

    public function test_tracking_modes_distinguish_individual_and_aggregate_inventory(): void
    {
        $this->assertTrue(TrackingModeEnum::STRUCTURED_TAG->hasIndividualUnits());
        $this->assertTrue(TrackingModeEnum::SEAT_NUMBER->hasIndividualUnits());
        $this->assertFalse(TrackingModeEnum::AGGREGATE->hasIndividualUnits());
        $this->assertSame('Structured Tag', TrackingModeEnum::STRUCTURED_TAG->label());
        $this->assertSame('Seat Number', TrackingModeEnum::SEAT_NUMBER->label());
        $this->assertSame('Aggregate', TrackingModeEnum::AGGREGATE->label());
        $this->assertStringContainsString('identifier unit', TrackingModeEnum::AGGREGATE->description());
    }

    public function test_category_values_are_the_complete_public_category_vocabulary(): void
    {
        $this->assertSame([
            'PC', 'Monitor', 'Keyboard', 'Mouse', 'TV', 'Laptop', 'Printer',
            'Scanner', 'Router', 'Switch', 'AC', 'Proyektor', 'Bracket TV',
            'Meja', 'Kursi',
        ], CategoryEnum::values());
    }

    public function test_day_helper_maps_and_orders_all_days_with_safe_unknown_fallbacks(): void
    {
        $translations = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        foreach ($translations as $english => $indonesian) {
            $this->assertSame($indonesian, DayHelper::fromEnglish($english));
        }

        $this->assertSame('Senin', DayHelper::fromDateString('2026-10-12'));
        $this->assertSame(1, DayHelper::getOrder('Senin'));
        $this->assertSame(7, DayHelper::getOrder('Minggu'));
        $this->assertSame('Unknown', DayHelper::fromEnglish('Funday'));
        $this->assertSame('Unknown', DayHelper::fromIndex(8));
        $this->assertSame(99, DayHelper::getOrder('Unknown'));
    }

    public function test_refinitiv_accessors_translate_known_values_and_preserve_custom_values(): void
    {
        $request = new RefinitivRequest([
            'affiliation' => 'internal_feb',
            'applicant_type' => 'mahasiswa',
            'purpose' => 'lainnya',
            'purpose_other' => 'Analisis kebijakan',
            'session' => 'sesi_3',
            'attendance_status' => 'tidak_hadir',
        ]);

        $this->assertTrue($request->isStudent());
        $this->assertFalse($request->isLecturer());
        $this->assertSame('Internal FEB Undip', $request->affiliation_label);
        $this->assertSame('Analisis kebijakan', $request->purpose_label);
        $this->assertSame('Sesi 3: 13.00 - 15.00 WIB / 13.30 - 15.30 (Jumat)', $request->session_label);
        $this->assertSame(['start' => '13:00', 'end' => '15:00'], $request->session_time);
        $this->assertSame('Tidak Hadir', $request->attendance_status_label);
    }

    public function test_borrowing_overdue_and_replacement_boundaries_follow_current_date(): void
    {
        $this->travelTo('2026-10-15 09:00:00');
        $borrowing = new AssetBorrowing;
        $borrowing->forceFill([
            'status' => 'borrowed',
            'return_date' => '2026-10-14',
            'is_damaged_on_return' => false,
        ]);
        $this->assertTrue($borrowing->isOverdue());

        $borrowing->forceFill([
            'status' => 'returned',
            'is_damaged_on_return' => true,
            'is_replaced' => false,
            'replacement_deadline' => '2026-10-14',
        ]);
        $this->assertTrue($borrowing->isReplacementOverdue());
        $this->assertTrue($borrowing->isReplacementPending());
        $this->assertSame('Penggantian Terlambat', $borrowing->getStatusLabel());
        $this->assertSame('bg-red-100 text-red-800', $borrowing->getStatusBadgeColor());

        $borrowing->forceFill(['is_replaced' => true]);
        $this->assertFalse($borrowing->isReplacementOverdue());
        $this->assertFalse($borrowing->isReplacementPending());
        $this->assertSame('Sudah Dikembalikan', $borrowing->getStatusLabel());
    }

    public function test_inventory_balance_calculates_sequential_custom_and_fallback_codes(): void
    {
        $sequential = new InventoryBalance([
            'quantity' => 3,
            'university_asset_code_prefix' => 'UA.X7',
        ]);
        $this->assertSame(['UA.X7', 'UA.X8', 'UA.X9'], $sequential->calculated_codes);

        $fallback = new InventoryBalance([
            'quantity' => 2,
            'university_asset_code_prefix' => 'KABEL',
        ]);
        $this->assertSame(['KABEL-1', 'KABEL-2'], $fallback->calculated_codes);

        $custom = new InventoryBalance([
            'quantity' => 2,
            'university_asset_code_prefix' => 'IGNORED',
            'custom_codes' => ['CUSTOM-2', 'CUSTOM-9'],
        ]);
        $this->assertSame(['CUSTOM-2', 'CUSTOM-9'], $custom->calculated_codes);
    }
}
