<?php

namespace Tests\Feature;

use App\Models\BadmintonMatch;
use App\Models\Category;
use App\Models\Competition;
use App\Models\User;
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

    public function test_export_excel_all_pools(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $cat = Category::create([
            'name' => 'Olahraga Excel',
            'slug' => 'olahraga-excel',
        ]);

        $comp = Competition::create([
            'category_id' => $cat->id,
            'name' => 'Bulu Tangkis Turnamen',
            'code' => 'BLT',
            'slug' => 'bulu-tangkis-turnamen',
            'type' => 'individual',
            'status' => 'open',
        ]);

        BadmintonMatch::create([
            'competition_id' => $comp->id,
            'match_code' => 'kat_c_pi-R1-M1',
            'court_number' => 'Lapangan 1',
            'round_name' => 'Babak 1',
            'category' => 'WS',
            'match_type' => 'single',
            'team1_player1' => 'Zahra',
            'team1_school' => 'SD 1',
            'team2_player1' => 'Nabila',
            'team2_school' => 'MI 2',
            'match_status' => 'upcoming',
            'match_order' => 1,
            'scheduled_time' => '08:00',
        ]);

        $response = $this->actingAs($admin)->get(route('pic.bracket.export_excel', $comp->id).'?pool=all');
        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }

    public function test_export_excel_single_pool(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $cat = Category::create([
            'name' => 'Olahraga Excel 2',
            'slug' => 'olahraga-excel-2',
        ]);

        $comp = Competition::create([
            'category_id' => $cat->id,
            'name' => 'Bulu Tangkis Turnamen 2',
            'code' => 'BLT',
            'slug' => 'bulu-tangkis-turnamen-2',
            'type' => 'individual',
            'status' => 'open',
        ]);

        BadmintonMatch::create([
            'competition_id' => $comp->id,
            'match_code' => 'kat_c_pi-R1-M1',
            'court_number' => 'Lapangan 1',
            'round_name' => 'Babak 1',
            'category' => 'WS',
            'match_type' => 'single',
            'team1_player1' => 'Zahra',
            'team1_school' => 'SD 1',
            'team2_player1' => 'Nabila',
            'team2_school' => 'MI 2',
            'match_status' => 'upcoming',
            'match_order' => 1,
            'scheduled_time' => '08:00',
        ]);

        $response = $this->actingAs($admin)->get(route('pic.bracket.export_excel', $comp->id).'?pool=kat_c_pi');
        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }

    public function test_matches_are_ordered_for_tournament_by_schedule_time_and_order(): void
    {
        $comp = Competition::create([
            'category_id' => Category::firstOrCreate(['slug' => 'test-cat'], ['name' => 'Test'])->id,
            'name' => 'Bulu Tangkis Urutan Test',
            'code' => 'BLT',
            'slug' => 'bulu-tangkis-urutan-test',
            'type' => 'individual',
            'status' => 'open',
        ]);

        // M1: Babak 1 jam 09.00
        $m1 = BadmintonMatch::create([
            'competition_id' => $comp->id,
            'match_code' => 'kat_c_pi-R1-M4',
            'court_number' => 'Lapangan 1',
            'round_name' => 'Babak 1',
            'match_status' => 'upcoming',
            'match_order' => 4,
            'scheduled_time' => '09.00',
            'team1_school' => 'SD A',
            'team1_player1' => 'P1 A',
            'team2_school' => 'SD B',
            'team2_player1' => 'P2 B',
        ]);

        // M2: Perebutan Juara 3 jam 13.00 (dibuat belakangan)
        $m2 = BadmintonMatch::create([
            'competition_id' => $comp->id,
            'match_code' => 'kat_c_pi-BRONZE-M1',
            'court_number' => 'Lapangan 1',
            'round_name' => 'Perebutan Juara 3',
            'match_status' => 'upcoming',
            'match_order' => 22,
            'scheduled_time' => '13.00',
            'team1_school' => 'SD C',
            'team1_player1' => 'P1 C',
            'team2_school' => 'SD D',
            'team2_player1' => 'P2 D',
        ]);

        // M3: Pertandingan LIVE sedang berlangsung jam 08.00
        $m3 = BadmintonMatch::create([
            'competition_id' => $comp->id,
            'match_code' => 'kat_c_pi-R1-M1',
            'court_number' => 'Lapangan 2',
            'round_name' => 'Babak 1',
            'match_status' => 'ongoing',
            'match_order' => 1,
            'scheduled_time' => '08.00',
            'team1_school' => 'SD E',
            'team1_player1' => 'P1 E',
            'team2_school' => 'SD F',
            'team2_player1' => 'P2 F',
        ]);

        // M4: Pertandingan Selesai jam 07.30
        $m4 = BadmintonMatch::create([
            'competition_id' => $comp->id,
            'match_code' => 'kat_c_pi-R1-M0',
            'court_number' => 'Lapangan 1',
            'round_name' => 'Babak Kualifikasi',
            'match_status' => 'finished',
            'match_order' => 0,
            'scheduled_time' => '07.30',
            'team1_school' => 'SD G',
            'team1_player1' => 'P1 G',
            'team2_school' => 'SD H',
            'team2_player1' => 'P2 H',
        ]);

        $orderedIds = BadmintonMatch::where('competition_id', $comp->id)
            ->orderedForTournament('schedule')
            ->pluck('id')
            ->all();

        // 1. Ongoing/LIVE harus paling depan ($m3)
        // 2. Upcoming jam 09.00 ($m1) harus sebelum upcoming jam 13.00 ($m2)
        // 3. Finished ($m4) harus di urutan belakang
        $this->assertEquals([$m3->id, $m1->id, $m2->id, $m4->id], $orderedIds);
    }
}
