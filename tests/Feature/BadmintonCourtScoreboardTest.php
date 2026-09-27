<?php

namespace Tests\Feature;

use App\Models\BadmintonMatch;
use App\Models\Category;
use App\Models\Competition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadmintonCourtScoreboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_arena_scoreboard_route_is_accessible(): void
    {
        $response = $this->get(route('badminton.arena'));
        $response->assertStatus(200);
        $response->assertSee('ARENA MULTI-LAPANGAN');
    }

    public function test_single_court_scoreboard_by_parameter(): void
    {
        $response = $this->get('/badminton/scoreboard?court=Lapangan 1');
        $response->assertStatus(200);
        $response->assertSee('AUTO TV:');
    }

    public function test_single_court_scoreboard_by_alias_route(): void
    {
        $response = $this->get(route('badminton.court.scoreboard', 'Lapangan 2'));
        $response->assertStatus(200);
        $response->assertSee('AUTO TV:');
    }

    public function test_api_active_courts_endpoint_returns_json(): void
    {
        $cat = Category::create([
            'name' => 'Olahraga',
            'slug' => 'olahraga',
        ]);

        $comp = Competition::create([
            'category_id' => $cat->id,
            'name' => 'Bulu Tangkis Tunggal Putra',
            'code' => 'BLT',
            'slug' => 'bulu-tangkis-tunggal-putra',
            'type' => 'individual',
            'status' => 'open',
        ]);

        BadmintonMatch::create([
            'competition_id' => $comp->id,
            'match_code' => 'MS-R1-M1',
            'court_number' => 'Lapangan 1',
            'round_name' => 'Babak 1',
            'category' => 'Tunggal Putra',
            'match_type' => 'single',
            'team1_player1' => 'Ahmad',
            'team1_school' => 'MTsN 1',
            'team2_player1' => 'Budi',
            'team2_school' => 'SMP 2',
            'match_status' => 'ongoing',
            'current_set' => 1,
            'team1_set1' => 11,
            'team2_set1' => 8,
            'started_at' => now(),
        ]);

        $response = $this->getJson(route('api.badminton.active_courts'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'Lapangan 1' => [
                'id',
                'court_number',
                'team1_player1',
                'team2_player1',
                'match_status',
                'team1_set1',
                'team2_set1',
            ],
        ]);

        $this->assertEquals('Ahmad', $response->json('Lapangan 1.team1_player1'));
    }

    public function test_single_court_scoreboard_with_existing_match(): void
    {
        $cat = Category::create([
            'name' => 'Olahraga 2',
            'slug' => 'olahraga-2',
        ]);

        $comp = Competition::create([
            'category_id' => $cat->id,
            'name' => 'Bulu Tangkis Tunggal Putra 2',
            'code' => 'BLT2',
            'slug' => 'bulu-tangkis-tunggal-putra-2',
            'type' => 'individual',
            'status' => 'open',
        ]);

        BadmintonMatch::create([
            'competition_id' => $comp->id,
            'match_code' => 'MS-R1-M2',
            'court_number' => 'Lapangan 1',
            'round_name' => 'Babak 1',
            'category' => 'Tunggal Putra',
            'match_type' => 'single',
            'team1_player1' => 'Ahmad',
            'team1_school' => 'MTsN 1',
            'team2_player1' => 'Budi',
            'team2_school' => 'SMP 2',
            'match_status' => 'ongoing',
            'current_set' => 1,
            'team1_set1' => 11,
            'team2_set1' => 8,
            'started_at' => now(),
        ]);

        $response = $this->get('/badminton/court/Lapangan 1');
        $response->assertStatus(200);
        $response->assertSee('Ahmad');

        $responseEncoded = $this->get('/badminton/court/Lapangan%201');
        $responseEncoded->assertStatus(200);
    }

    public function test_single_court_scoreboard_with_upcoming_match(): void
    {
        $cat = Category::create([
            'name' => 'Olahraga 3',
            'slug' => 'olahraga-3',
        ]);

        $comp = Competition::create([
            'category_id' => $cat->id,
            'name' => 'Bulu Tangkis Tunggal Putra 3',
            'code' => 'BLT3',
            'slug' => 'bulu-tangkis-tunggal-putra-3',
            'type' => 'individual',
            'status' => 'open',
        ]);

        BadmintonMatch::create([
            'competition_id' => $comp->id,
            'match_code' => 'MS-R1-M3',
            'court_number' => 'Lapangan 1',
            'round_name' => 'Babak 1',
            'category' => 'Tunggal Putra',
            'match_type' => 'single',
            'team1_player1' => 'Citra',
            'team1_school' => 'MTsN 1',
            'team2_player1' => 'Dewi',
            'team2_school' => 'SMP 3',
            'match_status' => 'upcoming',
            'match_order' => 1,
            'scheduled_time' => '08:00',
            'current_set' => 1,
            'team1_set1' => 0,
            'team2_set1' => 0,
        ]);

        $response = $this->get('/badminton/court/Lapangan%201');
        $response->assertStatus(200);
        $response->assertSee('Citra');
        $response->assertSee('AUTO TV:');

        $apiResponse = $this->getJson(route('api.badminton.active_courts'));
        $apiResponse->assertStatus(200);
        $this->assertEquals('Citra', $apiResponse->json('Lapangan 1.team1_player1'));
    }
}
