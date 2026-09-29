<?php

namespace Tests\Unit;

use App\Http\Controllers\TournamentBracketController;
use PHPUnit\Framework\TestCase;

class OfficialScheduleMappingTest extends TestCase
{
    public function test_all_categories_have_official_schedules_mapped(): void
    {
        $testCases = [
            'kat_a_pa-R1-M1' => ['day' => 1, 'court' => 'Lapangan 2', 'time' => '08:00', 'order' => 1],
            'kat_a_pa-R4-M1' => ['day' => 4, 'court' => 'Lapangan 1', 'time' => '08:30', 'order' => 16],
            'kat_a_pi-R1-M2' => ['day' => 2, 'court' => 'Lapangan 2', 'time' => '08:00', 'order' => 1],
            'kat_a_pi-R4-M1' => ['day' => 4, 'court' => 'Lapangan 1', 'time' => '08:00', 'order' => 10],
            'kat_b_pi-R1-M2' => ['day' => 1, 'court' => 'Lapangan 2', 'time' => '10:40', 'order' => 1],
            'kat_b_pi-R4-M1' => ['day' => 4, 'court' => 'Lapangan 1', 'time' => '09:00', 'order' => 15],
            'kat_b_pa-R1-M1' => ['day' => 1, 'court' => 'Lapangan 2', 'time' => '13:00', 'order' => 1],
            'kat_b_pa-R5-M1' => ['day' => 4, 'court' => 'Lapangan 1', 'time' => '09:30', 'order' => 32],
            'ganda_pa-R1-M2' => ['day' => 2, 'court' => 'Lapangan 1', 'time' => '16:00', 'order' => 1],
            'ganda_pa-R4-M1' => ['day' => 4, 'court' => 'Lapangan 1', 'time' => '13:30', 'order' => 11],
            'kat_c_pi-R1-M3' => ['day' => 1, 'court' => 'Lapangan 1', 'time' => '08:00', 'order' => 1],
            'kat_c_pi-R5-M1' => ['day' => 4, 'court' => 'Lapangan 1', 'time' => '10:00', 'order' => 23],
            'kat_c_pa-R1-M1' => ['day' => 1, 'court' => 'Lapangan 1', 'time' => '10:20', 'order' => 1],
            'kat_c_pa-R4-M1' => ['day' => 3, 'court' => 'Lapangan 1', 'time' => '11:00', 'order' => 29],
            'kat_c_pa-R5-M1' => ['day' => 4, 'court' => 'Lapangan 1', 'time' => '13:00', 'order' => 32],
        ];

        foreach ($testCases as $matchCode => $expected) {
            $actual = TournamentBracketController::getOfficialScheduleForMatch($matchCode);
            $this->assertNotNull($actual, "Match code {$matchCode} must have an official schedule mapping.");
            $this->assertSame($expected['day'], $actual['day'], "Day mismatch for {$matchCode}");
            $this->assertSame($expected['court'], $actual['court'], "Court mismatch for {$matchCode}");
            $this->assertSame($expected['time'], $actual['time'], "Time mismatch for {$matchCode}");
            $this->assertSame($expected['order'], $actual['order'], "Order mismatch for {$matchCode}");
        }
    }

    public function test_no_schedule_court_conflicts_in_official_timetable(): void
    {
        $allCodes = [
            // Kat A PA
            'kat_a_pa-R1-M1', 'kat_a_pa-R1-M2', 'kat_a_pa-R1-M3', 'kat_a_pa-R1-M4',
            'kat_a_pa-R1-M5', 'kat_a_pa-R1-M6', 'kat_a_pa-R1-M7', 'kat_a_pa-R1-M8',
            'kat_a_pa-R2-M1', 'kat_a_pa-R2-M2', 'kat_a_pa-R2-M3', 'kat_a_pa-R2-M4',
            'kat_a_pa-R3-M1', 'kat_a_pa-R3-M2', 'kat_a_pa-3RD-M1', 'kat_a_pa-R4-M1',
            // Kat A PI
            'kat_a_pi-R1-M2', 'kat_a_pi-R1-M7',
            'kat_a_pi-R2-M1', 'kat_a_pi-R2-M2', 'kat_a_pi-R2-M3', 'kat_a_pi-R2-M4',
            'kat_a_pi-R3-M1', 'kat_a_pi-R3-M2', 'kat_a_pi-3RD-M1', 'kat_a_pi-R4-M1',
            // Kat B PI
            'kat_b_pi-R1-M2', 'kat_b_pi-R1-M3', 'kat_b_pi-R1-M4', 'kat_b_pi-R1-M5',
            'kat_b_pi-R1-M6', 'kat_b_pi-R1-M7', 'kat_b_pi-R1-M8',
            'kat_b_pi-R2-M1', 'kat_b_pi-R2-M2', 'kat_b_pi-R2-M3', 'kat_b_pi-R2-M4',
            'kat_b_pi-R3-M1', 'kat_b_pi-R3-M2', 'kat_b_pi-3RD-M1', 'kat_b_pi-R4-M1',
            // Kat B PA
            'kat_b_pa-R1-M1', 'kat_b_pa-R1-M2', 'kat_b_pa-R1-M3', 'kat_b_pa-R1-M4',
            'kat_b_pa-R1-M5', 'kat_b_pa-R1-M6', 'kat_b_pa-R1-M7', 'kat_b_pa-R1-M8',
            'kat_b_pa-R1-M9', 'kat_b_pa-R1-M10', 'kat_b_pa-R1-M11', 'kat_b_pa-R1-M12',
            'kat_b_pa-R1-M13', 'kat_b_pa-R1-M14', 'kat_b_pa-R1-M15', 'kat_b_pa-R1-M16',
            'kat_b_pa-R2-M1', 'kat_b_pa-R2-M2', 'kat_b_pa-R2-M3', 'kat_b_pa-R2-M4',
            'kat_b_pa-R2-M5', 'kat_b_pa-R2-M6', 'kat_b_pa-R2-M7', 'kat_b_pa-R2-M8',
            'kat_b_pa-R3-M1', 'kat_b_pa-R3-M2', 'kat_b_pa-R3-M3', 'kat_b_pa-R3-M4',
            'kat_b_pa-R4-M1', 'kat_b_pa-R4-M2', 'kat_b_pa-3RD-M1', 'kat_b_pa-R5-M1',
            // Ganda PA
            'ganda_pa-R1-M2', 'ganda_pa-R1-M6', 'ganda_pa-R1-M7',
            'ganda_pa-R2-M1', 'ganda_pa-R2-M2', 'ganda_pa-R2-M3', 'ganda_pa-R2-M4',
            'ganda_pa-R3-M1', 'ganda_pa-R3-M2', 'ganda_pa-3RD-M1', 'ganda_pa-R4-M1',
            // Kat C PI
            'kat_c_pi-R1-M3', 'kat_c_pi-R1-M6', 'kat_c_pi-R1-M7', 'kat_c_pi-R1-M10',
            'kat_c_pi-R1-M11', 'kat_c_pi-R1-M14', 'kat_c_pi-R1-M15',
            'kat_c_pi-R2-M1', 'kat_c_pi-R2-M2', 'kat_c_pi-R2-M3', 'kat_c_pi-R2-M4',
            'kat_c_pi-R2-M5', 'kat_c_pi-R2-M6', 'kat_c_pi-R2-M7', 'kat_c_pi-R2-M8',
            'kat_c_pi-R3-M1', 'kat_c_pi-R3-M2', 'kat_c_pi-R3-M3', 'kat_c_pi-R3-M4',
            'kat_c_pi-R4-M1', 'kat_c_pi-R4-M2', 'kat_c_pi-3RD-M1', 'kat_c_pi-R5-M1',
            // Kat C PA
            'kat_c_pa-R1-M1', 'kat_c_pa-R1-M2', 'kat_c_pa-R1-M3', 'kat_c_pa-R1-M4',
            'kat_c_pa-R1-M5', 'kat_c_pa-R1-M6', 'kat_c_pa-R1-M7', 'kat_c_pa-R1-M8',
            'kat_c_pa-R1-M9', 'kat_c_pa-R1-M10', 'kat_c_pa-R1-M11', 'kat_c_pa-R1-M12',
            'kat_c_pa-R1-M13', 'kat_c_pa-R1-M14', 'kat_c_pa-R1-M15', 'kat_c_pa-R1-M16',
            'kat_c_pa-R2-M1', 'kat_c_pa-R2-M2', 'kat_c_pa-R2-M3', 'kat_c_pa-R2-M4',
            'kat_c_pa-R2-M5', 'kat_c_pa-R2-M6', 'kat_c_pa-R2-M7', 'kat_c_pa-R2-M8',
            'kat_c_pa-R3-M1', 'kat_c_pa-R3-M2', 'kat_c_pa-R3-M3', 'kat_c_pa-R3-M4',
            'kat_c_pa-R4-M1', 'kat_c_pa-R4-M2', 'kat_c_pa-3RD-M1', 'kat_c_pa-R5-M1',
        ];

        $this->assertCount(139, $allCodes);

        $occupiedSlots = [];

        foreach ($allCodes as $code) {
            $sched = TournamentBracketController::getOfficialScheduleForMatch($code);
            $this->assertNotNull($sched, "Missing schedule for {$code}");

            $slotKey = "Day{$sched['day']}_{$sched['court']}_{$sched['time']}";
            $this->assertArrayNotHasKey(
                $slotKey,
                $occupiedSlots,
                "Slot conflict detected! {$code} collides with existing match at {$slotKey}"
            );

            $occupiedSlots[$slotKey] = $code;
        }
    }
}
