<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::with(['competitions' => function ($q) {
            $q->withCount('registrations');
        }])->orderBy('order')->get();

        // Ambil semua kompetisi sekali — dipakai untuk daftar lengkap & featured
        $competitions = Competition::with('category')->withCount('registrations')->orderBy('order')->get();
        $featuredCompetitions = $competitions->take(6);

        // Gabungkan 4 count query menjadi satu
        $statsRaw = Registration::selectRaw('
            COUNT(*) as total_participants,
            SUM(CASE WHEN status = "verified" THEN 1 ELSE 0 END) as verified_participants,
            COUNT(DISTINCT institution_name) as total_schools
        ')->first();

        $stats = [
            'total_competitions' => $competitions->count(),
            'total_participants' => (int) $statsRaw->total_participants,
            'verified_participants' => (int) $statsRaw->verified_participants,
            'total_schools' => (int) $statsRaw->total_schools,
        ];

        $timelines = Timeline::where('is_active', true)->orderBy('order', 'asc')->get();

        return view('public.home', compact('categories', 'competitions', 'stats', 'featuredCompetitions', 'timelines'));
    }

    public function competitionDetail($slug)
    {
        $competition = Competition::with([
            'category',
            'criteria',
            'pic',
            'registrations.members',
            'verifiedRegistrations.members',
        ])->where('slug', $slug)->firstOrFail();
        $verifiedCount = $competition->verifiedRegistrations->count();

        return view('public.competition-detail', compact('competition', 'verifiedCount'));
    }

    public function checkStatus(Request $request)
    {
        $query = trim($request->input('q', $request->input('code', '')));
        $results = collect();
        $userAccount = null;

        if ($query) {
            $userAccount = User::where('nisn', $query)
                ->orWhere('email', $query)
                ->first();

            $results = Registration::with(['competition.category', 'members', 'verifier', 'invoice', 'user'])
                ->where('registration_code', 'LIKE', "%{$query}%")
                ->orWhere('institution_name', 'LIKE', "%{$query}%")
                ->orWhere('participant_number', 'LIKE', "%{$query}%")
                ->orWhereHas('invoice', function ($q) use ($query) {
                    $q->where('invoice_number', 'LIKE', "%{$query}%");
                })
                ->orWhereHas('members', function ($q) use ($query) {
                    $q->where('full_name', 'LIKE', "%{$query}%")
                        ->orWhere('nisn', 'LIKE', "%{$query}%");
                })
                ->orWhereHas('user', function ($q) use ($query) {
                    $q->where('nisn', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->latest()
                ->take(15)
                ->get();
        }

        return view('public.check-status', compact('query', 'results', 'userAccount'));
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

        if (! $selectedCompetition && $competitions->isNotEmpty()) {
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

    /**
     * JSON API — leaderboard data saja (untuk AJAX refresh tanpa full reload).
     * GET /api/leaderboard/{slug}
     */
    public function apiLeaderboard($slug)
    {
        $competition = Competition::with(['criteria', 'registrations' => function ($q) {
            $q->where('status', 'verified')->with(['members', 'scores.details']);
        }])->where('slug', $slug)->first();

        if (! $competition) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $leaderboard = $competition->registrations->map(function ($reg) {
            $lockedScores = $reg->scores->where('is_locked', true);
            $avgScore = $lockedScores->isNotEmpty() ? round($lockedScores->avg('total_score'), 2) : 0;

            return [
                'draw_number' => $reg->draw_number ?? 999,
                'participant_number' => $reg->participant_number ?? '-',
                'display_name' => $reg->display_name,
                'institution_name' => $reg->institution_name,
                'total_score' => $avgScore,
                'has_score' => $lockedScores->isNotEmpty(),
                'score_count' => $lockedScores->count(),
            ];
        })->sortByDesc('total_score')->values();

        return response()->json([
            'is_live_score' => (bool) $competition->is_live_score,
            'leaderboard' => $leaderboard,
            'updated_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-cache, no-store');
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
            $pureName = $reg->team_name ?: ($firstMember?->full_name ?: 'Peserta #'.$reg->id);

            return [
                'id' => $reg->id,
                'name' => $pureName,
                'institution' => $reg->institution_name,
                'draw_number' => $reg->draw_number,
                'has_draw' => ! is_null($reg->draw_number),
                'seed_number' => $reg->seed_number,
                'is_seeded' => $reg->isSeeded(),
                'seed_label' => $reg->seed_label,
            ];
        });

        return view('public.spin-viewer', compact('competition', 'participants'));
    }

    /**
     * Layar Iklan & TV Signage Display Page (Full Screen TV / Proyektor)
     */
    public function tvSignage()
    {
        $sponsorLogos = json_decode(AppSetting::get('sponsor_logos', '[]'), true) ?: [];
        foreach ($sponsorLogos as $logo) {
            AdminSettingsController::ensurePublicStorageSync($logo);
        }

        $pamphletImages = json_decode(AppSetting::get('pamphlet_images', '[]'), true) ?: [];
        foreach ($pamphletImages as $img) {
            AdminSettingsController::ensurePublicStorageSync($img);
        }

        $activeSlides = $this->buildPreparedTvSlides($sponsorLogos, $pamphletImages);

        $settings = [
            'tv_signage_enabled' => AppSetting::get('tv_signage_enabled', '1'),
            'tv_signage_header_title' => AppSetting::get('tv_signage_header_title', AppSetting::get('event_name', 'TALENTA 2026 - MTsN 1 Blitar')),
            'tv_signage_header_subtitle' => AppSetting::get('tv_signage_header_subtitle', 'Pentas Seni & Kejuaraan Pelajar Tingkat Jawa Timur'),
            'tv_signage_running_text' => AppSetting::get('tv_signage_running_text', 'Selamat Datang di TALENTA 2026 MTsN 1 Blitar • Junjung Tinggi Sportivitas & Kreativitas • Terima Kasih Kepada Seluruh Sponsor dan Pihak Pendukung Acara • Sukseskan Prestasi Gemilang Bersama Kami!'),
            'tv_signage_show_sponsor_marquee' => AppSetting::get('tv_signage_show_sponsor_marquee', '1'),
            'tv_signage_show_clock' => AppSetting::get('tv_signage_show_clock', '1'),
            'tv_signage_transition' => AppSetting::get('tv_signage_transition', 'fade'),
            'tv_signage_default_duration' => (int) AppSetting::get('tv_signage_default_duration', '10'),
            'tv_signage_version' => AppSetting::get('tv_signage_version', 'v1'),
            'app_logo' => AppSetting::get('app_logo', null),
            'event_logo' => AppSetting::get('event_logo', null),
            'institution_name' => AppSetting::get('institution_name', 'MTs Negeri 1 Blitar'),
            'event_year' => AppSetting::get('event_year', '2026'),
        ];

        return view('public.tv-signage', compact('activeSlides', 'settings', 'sponsorLogos', 'pamphletImages'));
    }

    /**
     * Build prepared slides with complete absolute URLs and fallbacks
     */
    protected function buildPreparedTvSlides(array $sponsorLogos = [], array $pamphletImages = []): array
    {
        $allSlides = json_decode(AppSetting::get('tv_signage_slides', '[]'), true) ?: [];
        $activeCustomSlides = array_values(array_filter($allSlides, fn ($s) => ($s['is_active'] ?? true)));

        $prepared = [];

        foreach ($activeCustomSlides as $s) {
            $mediaUrl = null;
            if (! empty($s['media_path'])) {
                AdminSettingsController::ensurePublicStorageSync($s['media_path']);
                $clean = ltrim(str_replace(['public/', 'storage/'], '', $s['media_path']), '/');
                $mediaUrl = \Illuminate\Support\Str::startsWith($s['media_path'], ['http://', 'https://'])
                    ? $s['media_path']
                    : asset('storage/'.$clean);
            } elseif (! empty($s['video_url'])) {
                $mediaUrl = $s['video_url'];
            }

            // Only add slide if it has mediaUrl or valid type
            $prepared[] = [
                'id' => $s['id'] ?? ('slide_'.count($prepared)),
                'title' => $s['title'] ?? 'Slide Iklan',
                'type' => $s['type'] ?? 'image',
                'media_url' => $mediaUrl,
                'duration' => (int) ($s['duration'] ?? 10),
                'notes' => $s['notes'] ?? '',
                'is_custom' => true,
            ];
        }

        // If no custom slides were added by admin, generate magnificent default slides
        if (empty($prepared)) {
            // Slide 1: Event Hero Showcase
            $prepared[] = [
                'id' => 'default_slide_event',
                'title' => AppSetting::get('event_name', 'Milad ke-58 MTsN 1 Blitar'),
                'type' => 'default_event',
                'media_url' => null,
                'duration' => 12,
                'notes' => 'Pentas Seni & Kejuaraan Pelajar Tingkat Jawa Timur',
                'is_custom' => false,
            ];

            // Slide 2: Wall of Sponsors Showcase (24 Logos)
            if (! empty($sponsorLogos)) {
                $prepared[] = [
                    'id' => 'default_slide_sponsors',
                    'title' => 'Sponsor & Mitra Resmi',
                    'type' => 'default_sponsors',
                    'media_url' => null,
                    'duration' => 15,
                    'notes' => 'Terima kasih atas dukungan seluruh mitra sponsor',
                    'is_custom' => false,
                ];
            }

            // Slide 3+: Pamphlet images (if available)
            foreach ($pamphletImages as $idx => $pImg) {
                $cleanP = ltrim(str_replace(['public/', 'storage/'], '', $pImg), '/');
                $prepared[] = [
                    'id' => 'default_slide_pamphlet_'.$idx,
                    'title' => 'Pamflet & Jadwal Lomba',
                    'type' => 'image',
                    'media_url' => asset('storage/'.$cleanP),
                    'duration' => 12,
                    'notes' => 'Informasi Pelaksanaan & Petunjuk Teknis Lomba',
                    'is_custom' => false,
                ];
            }
        }

        return $prepared;
    }

    /**
     * API State for TV Signage (Allows dynamic polling / seamless live updates on Smart TV)
     */
    public function apiTvSignageState()
    {
        $sponsorLogos = json_decode(AppSetting::get('sponsor_logos', '[]'), true) ?: [];
        $pamphletImages = json_decode(AppSetting::get('pamphlet_images', '[]'), true) ?: [];
        $activeSlides = $this->buildPreparedTvSlides($sponsorLogos, $pamphletImages);

        return response()->json([
            'version' => AppSetting::get('tv_signage_version', 'v1'),
            'slides_count' => count($activeSlides),
            'slides' => $activeSlides,
            'sponsor_logos' => $sponsorLogos,
            'running_text' => AppSetting::get('tv_signage_running_text', ''),
            'header_title' => AppSetting::get('tv_signage_header_title', ''),
            'header_subtitle' => AppSetting::get('tv_signage_header_subtitle', ''),
            'show_sponsor_marquee' => AppSetting::get('tv_signage_show_sponsor_marquee', '1'),
            'show_clock' => AppSetting::get('tv_signage_show_clock', '1'),
            'transition' => AppSetting::get('tv_signage_transition', 'fade'),
            'server_time' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-cache, no-store');
    }
}
