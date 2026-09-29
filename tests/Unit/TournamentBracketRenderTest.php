<?php

namespace Tests\Unit;

use App\Http\Controllers\TournamentBracketController;
use App\Models\BadmintonMatch;
use App\Models\Competition;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class TournamentBracketRenderTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_doubles_slot_names_render_without_truncation(): void
    {
        $controller = new TournamentBracketController;

        $bracketData = [
            'bracket_size' => 16,
            'is_doubles' => true,
            'total_participants' => 16,
            'total_rounds' => 4,
            'rounds' => [
                1 => [
                    'round_index' => 1,
                    'round_name' => 'Babak 16 Besar',
                    'matches' => [
                        [
                            'team1' => [
                                'slot_number' => 1,
                                'seed_number' => 1,
                                'name' => 'RIFQI ATHAYA ISKANDAR / ALFIN NUR FADILAH',
                                'institution' => 'SDN 06 NGUNUT / MI ROUDHOTUN NURUL HUDA',
                            ],
                            'team2' => [
                                'slot_number' => 2,
                                'is_bye' => true,
                                'name' => '[BYE]',
                                'institution' => 'Bebas Babak 1',
                            ],
                        ],
                    ],
                ],
            ],
            'champion' => [
                'name' => 'RIFQI ATHAYA ISKANDAR / ALFIN NUR FADILAH',
            ],
        ];

        $svg = $controller->renderClassicBracketSvg($bracketData);

        // Player 1 and Player 2 should be rendered in full
        $this->assertStringContainsString('RIFQI ATHAYA ISKANDAR', $svg);
        $this->assertStringContainsString('ALFIN NUR FADILAH', $svg);
        // School should be rendered in full
        $this->assertStringContainsString('SDN 06 NGUNUT / MI ROUDHOTUN NURUL HUDA', $svg);
        // No ugly ellipsis truncation
        $this->assertStringNotContainsString('RIFQI ATHAYA ISKANDAR..', $svg);
        $this->assertStringNotContainsString('SDN 06 NGUNUT / MI ROUDHOTUN N..', $svg);
    }

    public function test_doubles_advancing_winner_renders_two_lines_without_truncation(): void
    {
        $controller = new TournamentBracketController;

        $bracketData = [
            'bracket_size' => 16,
            'is_doubles' => true,
            'total_participants' => 16,
            'total_rounds' => 4,
            'rounds' => [
                1 => [
                    'round_index' => 1,
                    'round_name' => 'Babak 16 Besar',
                    'matches' => [
                        [
                            'team1' => [
                                'slot_number' => 1,
                                'name' => 'GILBERT SYELDION ALFARO / BIMA APRILINO',
                                'institution' => 'MIN 1 KEDIRI / MIS NURUL HUDA',
                            ],
                            'team2' => ['slot_number' => 2, 'name' => 'Team 2', 'institution' => 'School 2'],
                            'winner' => [
                                'name' => 'GILBERT SYELDION ALFARO / BIMA APRILINO',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $svg = $controller->renderClassicBracketSvg($bracketData);

        $this->assertStringContainsString('GILBERT SYELDION ALFARO', $svg);
        $this->assertStringContainsString('BIMA APRILINO', $svg);
        $this->assertStringNotContainsString('GILBERT SYELDION ALFARO / ..', $svg);
    }

    public function test_bye_matches_do_not_render_day_or_schedule_on_advancing_stem(): void
    {
        $controller = new TournamentBracketController;

        $bracketData = [
            'bracket_size' => 16,
            'is_doubles' => false,
            'total_participants' => 10,
            'total_rounds' => 4,
            'rounds' => [
                1 => [
                    'round_index' => 1,
                    'round_name' => 'Babak 16 Besar',
                    'matches' => [
                        [
                            'match_number' => null,
                            'status' => 'bye_advance',
                            'is_bye1' => false,
                            'is_bye2' => true,
                            'team1' => [
                                'slot_number' => 1,
                                'seed_number' => 1,
                                'name' => 'Mikayla Azzahra Putri Tampati',
                                'institution' => 'MI Jatisalam Gombang',
                            ],
                            'team2' => [
                                'slot_number' => 2,
                                'is_bye' => true,
                                'name' => '[BYE]',
                                'institution' => 'Bebas Babak 1',
                            ],
                            'winner' => [
                                'name' => 'Mikayla Azzahra Putri Tampati',
                            ],
                            'existing_match' => (object) [
                                'match_day' => 1,
                                'court_number' => 'BYE',
                                'scheduled_time' => null,
                                'team1_set1' => 0,
                                'team2_set1' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $svg = $controller->renderClassicBracketSvg($bracketData);

        // Advancing player name should still be visible on the line
        $this->assertStringContainsString('Mikayla Azzahra Putri Tampati', $svg);
        // But H1 schedule text must NOT be rendered under the stem line
        $this->assertStringNotContainsString('>H1<', $svg);
    }

    public function test_multi_set_scores_render_all_played_sets_in_bracket_svg(): void
    {
        $controller = new TournamentBracketController;

        $bracketData = [
            'bracket_size' => 16,
            'is_doubles' => false,
            'total_participants' => 10,
            'total_rounds' => 4,
            'rounds' => [
                1 => [
                    'round_index' => 1,
                    'round_name' => 'Babak 16 Besar',
                    'matches' => [
                        [
                            'match_number' => 2,
                            'status' => 'finished',
                            'is_bye1' => false,
                            'is_bye2' => false,
                            'team1' => [
                                'slot_number' => 3,
                                'name' => 'Siti Salwa',
                                'institution' => 'MI Perwanida Blitar',
                            ],
                            'team2' => [
                                'slot_number' => 4,
                                'name' => 'Emilia Nathania Hagi',
                                'institution' => 'MIN 5 Blitar',
                            ],
                            'winner' => [
                                'name' => 'Siti Salwa',
                            ],
                            'existing_match' => (object) [
                                'match_day' => 1,
                                'court_number' => 'Lap 1',
                                'scheduled_time' => '16:20',
                                'team1_set1' => 21,
                                'team2_set1' => 7,
                                'team1_set2' => 21,
                                'team2_set2' => 15,
                                'team1_set3' => 0,
                                'team2_set3' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $svg = $controller->renderClassicBracketSvg($bracketData);

        $this->assertStringContainsString('Siti Salwa', $svg);
        $this->assertStringContainsString('21-7, 21-15', $svg);
    }

    public function test_badminton_match_day_label_is_strictly_anchored_to_29_sep_2026(): void
    {
        $match1 = new BadmintonMatch([
            'match_day' => 1,
            'match_day_label' => 'Hari 1 (Senin, 28 Sep)',
            'match_date' => '2026-09-28',
        ]);

        $this->assertEquals('Hari 1 (Selasa, 29 Sep)', $match1->match_day_label);
        $this->assertEquals('2026-09-29', $match1->match_date->format('Y-m-d'));

        $match2 = new BadmintonMatch([
            'match_day' => 2,
            'match_day_label' => 'Hari 2 (Selasa, 29 Sep)',
            'match_date' => '2026-09-29',
        ]);

        $this->assertEquals('Hari 2 (Rabu, 30 Sep)', $match2->match_day_label);
        $this->assertEquals('2026-09-30', $match2->match_date->format('Y-m-d'));

        $match3 = new BadmintonMatch(['match_day' => 3]);
        $this->assertEquals('Hari 3 (Kamis, 1 Okt)', $match3->match_day_label);
        $this->assertEquals('2026-10-01', $match3->match_date->format('Y-m-d'));

        $match4 = new BadmintonMatch(['match_day' => 4]);
        $this->assertEquals('Hari 4 (Jumat, 2 Okt)', $match4->match_day_label);
        $this->assertEquals('2026-10-02', $match4->match_date->format('Y-m-d'));
    }

    public function test_order_of_play_worksheet_includes_bye_matches(): void
    {
        $controller = new class extends TournamentBracketController
        {
            public function callBuildOrderOfPlayWorksheet($sheet, $competition, $targetPool, $matchesCollection, $simulationPlan, $poolMap, $treeMatchesByCode)
            {
                $this->buildOrderOfPlayWorksheet($sheet, $competition, $targetPool, $matchesCollection, $simulationPlan, $poolMap, $treeMatchesByCode);
            }
        };

        $competition = new Competition([
            'name' => 'Badminton Cup 2026',
            'code' => 'BLT',
        ]);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $treeMatchesByCode = [
            'kat_a_pi-R1-M1' => [
                'match_code' => 'kat_a_pi-R1-M1',
                'round_name' => 'Babak 16 Besar',
                'pool_key' => 'kat_a_pi',
                'category_title' => 'Tunggal Putri SD Kelas 1-2',
                'is_bye' => true,
                'is_bye1' => false,
                'is_bye2' => true,
                'team1_player' => 'Mikayla Azzahra Putri Tampati',
                'team1_school' => 'MI Jatisalam Gombang',
                'team2_player' => '[BYE]',
                'team2_school' => 'Bebas Babak 1',
            ],
            'kat_a_pi-R1-M2' => [
                'match_code' => 'kat_a_pi-R1-M2',
                'round_name' => 'Babak 16 Besar',
                'pool_key' => 'kat_a_pi',
                'category_title' => 'Tunggal Putri SD Kelas 1-2',
                'is_bye' => false,
                'team1_player' => 'Siti Salwa',
                'team1_school' => 'MI Perwanida Blitar',
                'team2_player' => 'Emilia Nathania Hagi',
                'team2_school' => 'MIN 5 Blitar',
            ],
        ];

        $rawMatches = collect([
            new BadmintonMatch([
                'match_code' => 'kat_a_pi-R1-M1',
                'match_day' => 1,
                'court_number' => 'BYE',
                'scheduled_time' => null,
                'match_order' => null,
                'round_name' => 'Babak 16 Besar',
                'category' => 'WS',
                'team1_player1' => 'Mikayla Azzahra Putri Tampati',
                'team1_school' => 'MI Jatisalam Gombang',
                'team2_player1' => '[BYE]',
                'team2_school' => 'Bebas Babak 1',
                'match_status' => 'finished',
                'winner_team' => 1,
            ]),
            new BadmintonMatch([
                'match_code' => 'kat_a_pi-R1-M2',
                'match_day' => 1,
                'court_number' => 'Lapangan 2',
                'scheduled_time' => '08:00',
                'match_order' => 1,
                'round_name' => 'Babak 16 Besar',
                'category' => 'WS',
                'team1_player1' => 'Siti Salwa',
                'team1_school' => 'MI Perwanida Blitar',
                'team2_player1' => 'Emilia Nathania Hagi',
                'team2_school' => 'MIN 5 Blitar',
                'match_status' => 'upcoming',
            ]),
        ]);

        $controller->callBuildOrderOfPlayWorksheet(
            $sheet,
            $competition,
            null,
            $rawMatches,
            null,
            ['kat_a_pi' => ['key' => 'kat_a_pi', 'title' => 'Tunggal Putri SD Kelas 1-2']],
            $treeMatchesByCode
        );

        // Verify that row 6 has the day header, row 7 has the contested match, and row 8 has the BYE match
        $foundBye = false;
        $foundContested = false;

        for ($r = 6; $r <= 15; $r++) {
            $courtVal = $sheet->getCell("D{$r}")->getValue();
            $p1Val = $sheet->getCell("H{$r}")->getValue();
            $p2Val = $sheet->getCell("K{$r}")->getValue();
            $statusVal = $sheet->getCell("M{$r}")->getValue();

            if ($courtVal === 'BYE') {
                $foundBye = true;
                $this->assertEquals('Mikayla Azzahra Putri Tampati', $p1Val);
                $this->assertEquals('[BYE]', $p2Val);
                $this->assertEquals('BYE (Lolos Otomatis)', $statusVal);
            }

            if ($courtVal === 'Lapangan 2') {
                $foundContested = true;
                $this->assertEquals('Siti Salwa', $p1Val);
                $this->assertEquals('Emilia Nathania Hagi', $p2Val);
            }
        }

        $this->assertTrue($foundContested, 'Contested match must be rendered in Order of Play');
        $this->assertTrue($foundBye, 'BYE match must be rendered in Order of Play');
    }
}
