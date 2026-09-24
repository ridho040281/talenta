<?php

namespace Tests\Unit;

use App\Http\Controllers\TournamentBracketController;
use PHPUnit\Framework\TestCase;

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
}
