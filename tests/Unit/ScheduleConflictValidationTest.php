<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ScheduleConflictValidationTest extends TestCase
{
    /**
     * Helper to detect schedule conflicts identical to the logic in TournamentBracketController.
     *
     * @param  array<int, array<string, mixed>>  $matches
     * @return array<string, array<string, mixed>>
     */
    protected function detectConflicts(array $matches): array
    {
        $normalizeTime = function ($val) {
            if (empty($val)) {
                return '';
            }
            $t = str_replace('.', ':', trim($val));
            $parts = explode(':', $t);
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                return sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
            }

            return $t;
        };

        $slotUsages = [];
        foreach ($matches as $em) {
            $court = $em['court_number'] ?? '';
            $time = $em['scheduled_time'] ?? '';
            if (! empty($court) && strtoupper($court) !== 'BYE' && ! empty($time)) {
                $timeClean = $normalizeTime($time);
                $dayVal = (int) ($em['match_day'] ?? 1);
                $dateVal = $em['match_date'] ?? "day_{$dayVal}";
                $slotKey = "{$dateVal}|".strtolower(trim($court))."|{$timeClean}";
                $slotUsages[$slotKey][] = [
                    'code' => $em['match_code'],
                    'order' => $em['match_order'] ?? null,
                    'court' => $court,
                    'time' => $time,
                    'day' => $dayVal,
                ];
            }
        }

        $scheduleConflicts = [];
        foreach ($slotUsages as $slotKey => $matchesInSlot) {
            if (count($matchesInSlot) > 1) {
                foreach ($matchesInSlot as $mItem) {
                    $otherDescriptions = [];
                    foreach ($matchesInSlot as $other) {
                        if ($other['code'] !== $mItem['code']) {
                            $otherPartai = $other['order'] ? "Partai #{$other['order']}" : $other['code'];
                            $otherDescriptions[] = $otherPartai;
                        }
                    }
                    $scheduleConflicts[$mItem['code']] = [
                        'court' => $mItem['court'],
                        'time' => $mItem['time'],
                        'day' => $mItem['day'],
                        'message' => 'Bentrok jadwal dengan '.implode(', ', $otherDescriptions),
                    ];
                }
            }
        }

        return $scheduleConflicts;
    }

    public function test_matches_with_same_court_and_time_are_flagged_as_conflicts(): void
    {
        $matches = [
            [
                'match_code' => 'kat_b_pa-R1-M3',
                'match_order' => 3,
                'court_number' => 'Lapangan 2',
                'scheduled_time' => '13.40',
                'match_day' => 1,
                'match_date' => '2026-09-29',
            ],
            [
                'match_code' => 'kat_b_pa-R1-M4',
                'match_order' => 4,
                'court_number' => 'Lapangan 2',
                'scheduled_time' => '13:40',
                'match_day' => 1,
                'match_date' => '2026-09-29',
            ],
            [
                'match_code' => 'kat_b_pa-R1-M5',
                'match_order' => 5,
                'court_number' => 'Lapangan 1',
                'scheduled_time' => '13.40',
                'match_day' => 1,
                'match_date' => '2026-09-29',
            ],
        ];

        $conflicts = $this->detectConflicts($matches);

        // M3 and M4 are on same court and time -> both should have conflict
        $this->assertArrayHasKey('kat_b_pa-R1-M3', $conflicts);
        $this->assertArrayHasKey('kat_b_pa-R1-M4', $conflicts);
        $this->assertStringContainsString('Partai #4', $conflicts['kat_b_pa-R1-M3']['message']);
        $this->assertStringContainsString('Partai #3', $conflicts['kat_b_pa-R1-M4']['message']);

        // M5 is on Lapangan 1 -> no conflict
        $this->assertArrayNotHasKey('kat_b_pa-R1-M5', $conflicts);
    }

    public function test_bye_matches_and_empty_times_are_not_flagged_as_conflicts(): void
    {
        $matches = [
            [
                'match_code' => 'kat_b_pa-R1-M1',
                'match_order' => 1,
                'court_number' => 'BYE',
                'scheduled_time' => '08:00',
                'match_day' => 1,
                'match_date' => '2026-09-29',
            ],
            [
                'match_code' => 'kat_b_pa-R1-M2',
                'match_order' => 2,
                'court_number' => 'BYE',
                'scheduled_time' => '08:00',
                'match_day' => 1,
                'match_date' => '2026-09-29',
            ],
            [
                'match_code' => 'kat_b_pa-R1-M6',
                'match_order' => 6,
                'court_number' => 'Lapangan 1',
                'scheduled_time' => '',
                'match_day' => 1,
                'match_date' => '2026-09-29',
            ],
        ];

        $conflicts = $this->detectConflicts($matches);
        $this->assertEmpty($conflicts);
    }
}
