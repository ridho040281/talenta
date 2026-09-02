<?php

namespace App\Http\Controllers;

use App\Models\BadmintonMatch;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BadmintonMatchController extends Controller
{
    public function index(Request $request)
    {
        $query = BadmintonMatch::with(['competition', 'umpire'])->latest();

        if ($request->filled('court')) {
            $query->where('court_number', $request->court);
        }

        if ($request->filled('status')) {
            $query->where('match_status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $matches = $query->paginate(15);
        $competitions = Competition::where('code', 'BLT')->orWhere('name', 'like', '%Bulu Tangkis%')->orWhere('name', 'like', '%Badminton%')->get();
        if ($competitions->isEmpty()) {
            $competitions = Competition::all();
        }

        $courts = BadmintonMatch::select('court_number')->distinct()->pluck('court_number');
        if ($courts->isEmpty()) {
            $courts = collect(['Lapangan 1', 'Lapangan 2', 'Lapangan 3']);
        }

        return view('badminton.index', compact('matches', 'competitions', 'courts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => 'nullable|exists:competitions,id',
            'court_number'   => 'required|string|max:50',
            'match_code'     => 'nullable|string|max:50',
            'round_name'     => 'required|string|max:100',
            'category'       => 'required|string|in:MS,WS,MD,WD,XD',
            'match_type'     => 'required|string|in:single,double',
            'team1_school'   => 'required|string|max:150',
            'team1_player1'  => 'required|string|max:150',
            'team1_player2'  => 'nullable|string|max:150',
            'team2_school'   => 'required|string|max:150',
            'team2_player1'  => 'required|string|max:150',
            'team2_player2'  => 'nullable|string|max:150',
        ]);

        $validated['current_set'] = 1;
        $validated['team1_set1'] = 0;
        $validated['team2_set1'] = 0;
        $validated['team1_set2'] = 0;
        $validated['team2_set2'] = 0;
        $validated['team1_set3'] = 0;
        $validated['team2_set3'] = 0;
        $validated['server_team'] = 1;
        $validated['server_player'] = 1;
        $validated['match_status'] = 'upcoming';
        $validated['scores_history'] = [];

        $match = BadmintonMatch::create($validated);

        return redirect()->route('badminton.index')->with('success', "Pertandingan {$match->match_code} ({$match->court_number}) berhasil dibuat!");
    }

    public function update(Request $request, $id)
    {
        $match = BadmintonMatch::findOrFail($id);

        $validated = $request->validate([
            'competition_id' => 'nullable|exists:competitions,id',
            'court_number'   => 'required|string|max:50',
            'match_code'     => 'nullable|string|max:50',
            'round_name'     => 'required|string|max:100',
            'category'       => 'required|string|in:MS,WS,MD,WD,XD',
            'match_type'     => 'required|string|in:single,double',
            'team1_school'   => 'required|string|max:150',
            'team1_player1'  => 'required|string|max:150',
            'team1_player2'  => 'nullable|string|max:150',
            'team2_school'   => 'required|string|max:150',
            'team2_player1'  => 'required|string|max:150',
            'team2_player2'  => 'nullable|string|max:150',
        ]);

        $match->update($validated);

        return redirect()->route('badminton.index')->with('success', 'Data pertandingan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $match = BadmintonMatch::findOrFail($id);
        $match->delete();

        return redirect()->route('badminton.index')->with('success', 'Pertandingan berhasil dihapus.');
    }

    public function umpire($id)
    {
        $match = BadmintonMatch::with('competition')->findOrFail($id);
        $appSettings = AppSetting::getAllSettings();

        // Assign current user as umpire if not set
        if (!$match->umpire_id && Auth::check()) {
            $match->update(['umpire_id' => Auth::id()]);
        }

        return view('badminton.umpire', compact('match', 'appSettings'));
    }

    public function apiScore(Request $request, $id)
    {
        $match = BadmintonMatch::findOrFail($id);
        $action = $request->input('action');

        $history = $match->scores_history ?? [];

        // Save snapshot for Undo
        if (in_array($action, ['add_point', 'set_server', 'next_set', 'set_status'])) {
            $snapshot = [
                'current_set'   => $match->current_set,
                'team1_set1'    => $match->team1_set1,
                'team2_set1'    => $match->team2_set1,
                'team1_set2'    => $match->team1_set2,
                'team2_set2'    => $match->team2_set2,
                'team1_set3'    => $match->team1_set3,
                'team2_set3'    => $match->team2_set3,
                'server_team'   => $match->server_team,
                'server_player' => $match->server_player,
                'match_status'  => $match->match_status,
                'winner_team'   => $match->winner_team,
            ];
            $history[] = $snapshot;
            if (count($history) > 40) {
                array_shift($history);
            }
        }

        if ($action === 'add_point') {
            $team = (int) $request->input('team');
            $set = $match->current_set;

            if ($match->match_status === 'upcoming') {
                $match->match_status = 'ongoing';
                $match->started_at = now();
            }

            // Increment score
            $t1Key = "team1_set{$set}";
            $t2Key = "team2_set{$set}";

            if ($team === 1) {
                $match->{$t1Key}++;
                if ($match->match_type === 'double') {
                    if ($match->server_team === 1) {
                        $match->server_player = ($match->server_player === 1) ? 2 : 1;
                    } else {
                        $match->server_team = 1;
                        $match->server_player = ($match->{$t1Key} % 2 === 0) ? 1 : 2;
                    }
                } else {
                    $match->server_team = 1;
                    $match->server_player = 1;
                }
            } else {
                $match->{$t2Key}++;
                if ($match->match_type === 'double') {
                    if ($match->server_team === 2) {
                        $match->server_player = ($match->server_player === 1) ? 2 : 1;
                    } else {
                        $match->server_team = 2;
                        $match->server_player = ($match->{$t2Key} % 2 === 0) ? 1 : 2;
                    }
                } else {
                    $match->server_team = 2;
                    $match->server_player = 1;
                }
            }

            // Check if game won
            $s1 = $match->{$t1Key};
            $s2 = $match->{$t2Key};
            if ((($s1 >= 21 || $s2 >= 21) && abs($s1 - $s2) >= 2) || max($s1, $s2) === 30) {
                $setsWon = $match->getSetsWon();
                if ($setsWon['t1'] >= 2) {
                    $match->match_status = 'finished';
                    $match->winner_team = 1;
                    $match->finished_at = now();
                } elseif ($setsWon['t2'] >= 2) {
                    $match->match_status = 'finished';
                    $match->winner_team = 2;
                    $match->finished_at = now();
                }
            }

            $match->scores_history = $history;
            $match->save();
        } elseif ($action === 'undo') {
            if (!empty($history)) {
                $last = array_pop($history);
                $match->fill($last);
                $match->scores_history = $history;
                $match->save();
            }
        } elseif ($action === 'set_server') {
            $match->server_team = (int) $request->input('team');
            $match->server_player = (int) $request->input('player', 1);
            $match->scores_history = $history;
            $match->save();
        } elseif ($action === 'next_set') {
            if ($match->current_set < 3) {
                $match->current_set++;
                $match->server_team = 1;
                $match->server_player = 1;
                $match->match_status = 'ongoing';
                $match->scores_history = $history;
                $match->save();
            }
        } elseif ($action === 'set_status') {
            $match->match_status = $request->input('status');
            $match->scores_history = $history;
            $match->save();
        } elseif ($action === 'reset') {
            $match->current_set = 1;
            $match->team1_set1 = 0;
            $match->team2_set1 = 0;
            $match->team1_set2 = 0;
            $match->team2_set2 = 0;
            $match->team1_set3 = 0;
            $match->team2_set3 = 0;
            $match->server_team = 1;
            $match->server_player = 1;
            $match->match_status = 'upcoming';
            $match->winner_team = null;
            $match->scores_history = [];
            $match->save();
        }

        return response()->json([
            'success' => true,
            'match'   => $this->formatMatchState($match),
        ]);
    }

    public function apiState($id)
    {
        $match = BadmintonMatch::find($id);
        if (!$match) {
            return response()->json(['error' => 'Match not found'], 404);
        }
        return response()->json($this->formatMatchState($match))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function scoreboard(Request $request, $id = null)
    {
        $appSettings = AppSetting::getAllSettings();

        $match = null;
        if ($id) {
            $match = BadmintonMatch::with('competition')->find($id);
        }

        if (!$match) {
            // Find active ongoing match or latest match
            $match = BadmintonMatch::with('competition')
                ->where('match_status', 'ongoing')
                ->latest('updated_at')
                ->first();

            if (!$match) {
                $match = BadmintonMatch::with('competition')->latest()->first();
            }
        }

        $allMatches = BadmintonMatch::latest()->take(20)->get();

        return view('badminton.scoreboard', compact('match', 'allMatches', 'appSettings'));
    }

    public function arenaScoreboard(Request $request)
    {
        $appSettings = AppSetting::getAllSettings();

        // Get distinct courts
        $courts = BadmintonMatch::select('court_number')->distinct()->orderBy('court_number')->pluck('court_number');
        if ($courts->isEmpty()) {
            $courts = collect(['Lapangan 1', 'Lapangan 2']);
        }

        // Get the most relevant match for each court (ongoing first, then upcoming, then finished)
        $courtMatches = [];
        foreach ($courts as $court) {
            $m = BadmintonMatch::where('court_number', $court)
                ->whereIn('match_status', ['ongoing', 'interval'])
                ->latest('updated_at')
                ->first();

            if (!$m) {
                $m = BadmintonMatch::where('court_number', $court)
                    ->where('match_status', 'upcoming')
                    ->first();
            }

            if (!$m) {
                $m = BadmintonMatch::where('court_number', $court)
                    ->latest('updated_at')
                    ->first();
            }

            if ($m) {
                $courtMatches[$court] = $this->formatMatchState($m);
            }
        }

        return view('badminton.arena', compact('courts', 'courtMatches', 'appSettings'));
    }

    public function apiActiveCourts()
    {
        $courts = BadmintonMatch::select('court_number')->distinct()->orderBy('court_number')->pluck('court_number');
        if ($courts->isEmpty()) {
            $courts = collect(['Lapangan 1', 'Lapangan 2']);
        }
        $courtMatches = [];
        foreach ($courts as $court) {
            $m = BadmintonMatch::where('court_number', $court)
                ->whereIn('match_status', ['ongoing', 'interval'])
                ->latest('updated_at')
                ->first();

            if (!$m) {
                $m = BadmintonMatch::where('court_number', $court)
                    ->where('match_status', 'upcoming')
                    ->first();
            }

            if (!$m) {
                $m = BadmintonMatch::where('court_number', $court)
                    ->latest('updated_at')
                    ->first();
            }

            if ($m) {
                $courtMatches[$court] = $this->formatMatchState($m);
            }
        }

        return response()->json($courtMatches)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function streamState($id)
    {
        $match = BadmintonMatch::find($id);
        if (!$match) {
            return response()->json(['error' => 'Match not found'], 404);
        }
        return response()->json($this->formatMatchState($match))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
    }

    public function arenaStream()
    {
        return $this->apiActiveCourts();
    }

    private function formatMatchState(BadmintonMatch $match): array
    {
        return [
            'id'             => $match->id,
            'court_number'   => $match->court_number,
            'match_code'     => $match->match_code,
            'round_name'     => $match->round_name,
            'category'       => $match->category,
            'match_type'     => $match->match_type,
            'team1_school'   => $match->team1_school,
            'team1_player1'  => $match->team1_player1,
            'team1_player2'  => $match->team1_player2,
            'team2_school'   => $match->team2_school,
            'team2_player1'  => $match->team2_player1,
            'team2_player2'  => $match->team2_player2,
            'current_set'    => $match->current_set,
            'team1_set1'     => $match->team1_set1,
            'team2_set1'     => $match->team2_set1,
            'team1_set2'     => $match->team1_set2,
            'team2_set2'     => $match->team2_set2,
            'team1_set3'     => $match->team1_set3,
            'team2_set3'     => $match->team2_set3,
            'server_team'    => $match->server_team,
            'server_player'  => $match->server_player,
            'match_status'   => $match->match_status,
            'winner_team'    => $match->winner_team,
            'sets_won'       => $match->getSetsWon(),
            'updated_at'     => $match->updated_at->toIso8601String(),
        ];
    }
}