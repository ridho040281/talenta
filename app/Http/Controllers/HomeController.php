<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\Score;
use App\Models\Timeline;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::with(['competitions' => function ($q) {
            $q->with(['registrations.members'])->withCount('registrations');
        }])->orderBy('order')->get();

        $competitions = Competition::with(['category', 'registrations.members'])->withCount('registrations')->get();

        $stats = [
            'total_competitions' => Competition::count(),
            'total_participants' => Registration::count(),
            'verified_participants' => Registration::where('status', 'verified')->count(),
            'total_schools' => Registration::distinct('institution_name')->count('institution_name'),
        ];

        $featuredCompetitions = Competition::with('category')->take(6)->get();
        $timelines = Timeline::where('is_active', true)->orderBy('order', 'asc')->get();

        return view('public.home', compact('categories', 'competitions', 'stats', 'featuredCompetitions', 'timelines'));
    }

    public function competitionDetail($slug)
    {
        $competition = Competition::with(['category', 'criteria', 'pic'])->where('slug', $slug)->firstOrFail();
        $verifiedCount = $competition->verifiedRegistrations()->count();

        return view('public.competition-detail', compact('competition', 'verifiedCount'));
    }

    public function checkStatus(Request $request)
    {
        $query = $request->input('q');
        $results = collect();

        if ($query) {
            $results = Registration::with(['competition', 'members'])
                ->where('registration_code', 'LIKE', "%{$query}%")
                ->orWhere('institution_name', 'LIKE', "%{$query}%")
                ->orWhere('participant_number', 'LIKE', "%{$query}%")
                ->orWhereHas('members', function ($q) use ($query) {
                    $q->where('full_name', 'LIKE', "%{$query}%")
                      ->orWhere('nisn', 'LIKE', "%{$query}%");
                })
                ->latest()
                ->take(15)
                ->get();
        }

        return view('public.check-status', compact('query', 'results'));
    }

    public function liveScoreboard(Request $request, $slug = null)
    {
        $categories = Category::with(['competitions' => function ($q) {
            $q->orderBy('name');
        }])->orderBy('order')->get();

        $competitions = Competition::with('category')->orderBy('name')->get();
        
        $selectedCompetition = null;
        if ($slug) {
            $selectedCompetition = Competition::with(['criteria', 'category', 'registrations' => function ($q) {
                $q->where('status', 'verified')->with(['members', 'scores.details']);
            }])->where('slug', $slug)->first();
        }

        if (!$selectedCompetition && $competitions->isNotEmpty()) {
            $selectedCompetition = Competition::with(['criteria', 'category', 'registrations' => function ($q) {
                $q->where('status', 'verified')->with(['members', 'scores.details']);
            }])->first();
        }

        $leaderboard = collect();
        if ($selectedCompetition) {
            $leaderboard = $selectedCompetition->registrations->map(function ($reg) {
                $lockedScores = $reg->scores->where('is_locked', true);
                $avgScore = $lockedScores->isNotEmpty() ? round($lockedScores->avg('total_score'), 2) : 0;
                $hasScore = $lockedScores->isNotEmpty();

                return [
                    'registration' => $reg,
                    'draw_number' => $reg->draw_number ?? 999,
                    'participant_number' => $reg->participant_number ?? '-',
                    'display_name' => $reg->display_name,
                    'institution_name' => $reg->institution_name,
                    'total_score' => $avgScore,
                    'has_score' => $hasScore,
                    'score_count' => $lockedScores->count(),
                ];
            })->sortByDesc('total_score')->values();
        }

        return view('public.live-scoreboard', compact('categories', 'competitions', 'selectedCompetition', 'leaderboard'));
    }

    public function spinViewer($slug)
    {
        $competition = Competition::with(['category', 'registrations' => function ($q) {
            $q->where('status', 'verified')->with('members');
        }])->where(function ($query) use ($slug) {
            $query->where('slug', $slug)
                  ->orWhere('code', strtoupper($slug));
        })->firstOrFail();

        $participants = $competition->registrations->map(function ($reg) {
            $firstMember = $reg->members->first();
            $pureName = $reg->team_name ?: ($firstMember?->full_name ?: 'Peserta #' . $reg->id);

            return [
                'id' => $reg->id,
                'name' => $pureName,
                'institution' => $reg->institution_name,
                'draw_number' => $reg->draw_number,
                'has_draw' => !is_null($reg->draw_number),
            ];
        });

        return view('public.spin-viewer', compact('competition', 'participants'));
    }
}
