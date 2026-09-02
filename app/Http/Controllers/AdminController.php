<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Competition;
use App\Models\CompetitionCriterion;
use App\Models\Registration;
use App\Models\Score;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_participants' => User::where('role', 'peserta')->count(),
            'total_judges' => User::where('role', 'juri')->count(),
            'total_pics' => User::where('role', 'pic_lomba')->count(),
            'total_competitions' => Competition::count(),
            'total_registrations' => Registration::count(),
            'verified_registrations' => Registration::where('status', 'verified')->count(),
            'pending_registrations' => Registration::where('status', 'pending')->count(),
            'scores_entered' => Score::where('is_locked', true)->count(),
        ];

        $categories = Category::withCount('competitions')->get();
        $recentRegistrations = Registration::with(['competition.category', 'members'])->latest()->take(8)->get();
        $competitions = Competition::with(['category', 'pic'])->withCount(['registrations', 'verifiedRegistrations'])->get();

        return view('admin.dashboard', compact('stats', 'categories', 'recentRegistrations', 'competitions'));
    }

    public function competitions()
    {
        $competitions = Competition::with(['category', 'pic', 'criteria'])->withCount('registrations')->get();
        $categories = Category::with(['competitions.pic', 'competitions' => function($q) {
            $q->withCount('registrations');
        }])->withCount('competitions')->orderBy('order', 'asc')->get();
        $pics = User::where('role', 'pic_lomba')->orWhere('role', 'superadmin')->get();
        $timelines = Timeline::orderBy('order', 'asc')->get();

        return view('admin.competitions', compact('competitions', 'categories', 'pics', 'timelines'));
    }

    public function editCompetitionPage($id)
    {
        $competition = Competition::with(['category', 'pic', 'criteria'])->withCount('registrations')->findOrFail($id);
        $categories = Category::orderBy('order', 'asc')->get();
        $pics = User::where('role', 'pic_lomba')->orWhere('role', 'superadmin')->get();

        return view('admin.competitions-edit', compact('competition', 'categories', 'pics'));
    }

    public function storeCompetition(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:competitions,code'],
            'type' => ['required', 'in:individu,tim,kelompok,regu,kolektif'],
            'min_members' => ['required', 'integer', 'min:1'],
            'max_members' => ['required', 'integer', 'min:1'],
            'quota' => ['required', 'integer', 'min:0'],
            'registration_fee' => ['nullable', 'numeric', 'min:0'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'venue' => ['nullable', 'string'],
            'schedule_date' => ['nullable', 'date'],
            'schedule_time' => ['nullable', 'string'],
            'rules' => ['nullable', 'string'],
            'guidelines_file' => ['nullable', 'string'],
            'guidelines_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'order' => ['nullable', 'integer'],
        ]);

        $guidelinesPath = $request->input('guidelines_file');
        if ($request->hasFile('guidelines_pdf')) {
            $guidelinesPath = $request->file('guidelines_pdf')->store('guidelines', 'public');
        }

        $nextOrder = $validated['order'] ?? (Competition::withoutGlobalScope('order')->max('order') + 1);

        $competition = Competition::create([
            'category_id' => $validated['category_id'],
            'pic_id' => $validated['pic_id'] ?? null,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'min_members' => $validated['min_members'],
            'max_members' => $validated['max_members'],
            'quota' => $validated['quota'],
            'registration_fee' => $validated['registration_fee'] ?? 0,
            'venue' => $validated['venue'] ?? null,
            'schedule_date' => $validated['schedule_date'] ?? null,
            'schedule_time' => $validated['schedule_time'] ?? null,
            'rules' => $validated['rules'] ?? null,
            'guidelines_file' => $guidelinesPath,
            'order' => $nextOrder,
            'status' => 'buka',
            'is_live_score' => $request->boolean('is_live_score', false),
        ]);

        // Default / Custom criteria
        if ($request->has('criteria') && is_array($request->criteria)) {
            foreach ($request->criteria as $crit) {
                if (!empty($crit['name'])) {
                    CompetitionCriterion::create([
                        'competition_id' => $competition->id,
                        'name' => $crit['name'],
                        'weight_percentage' => (int) ($crit['weight_percentage'] ?? 100),
                        'min_score' => (float) ($crit['min_score'] ?? 0),
                        'max_score' => (float) ($crit['max_score'] ?? 100),
                        'description' => $crit['description'] ?? null,
                    ]);
                }
            }
        }

        if ($competition->criteria()->count() === 0) {
            CompetitionCriterion::create([
                'competition_id' => $competition->id,
                'name' => 'Penilaian Umum',
                'weight_percentage' => 100,
                'min_score' => 0,
                'max_score' => 100,
            ]);
        }

        return redirect()->route('admin.competitions')->with('success', 'Cabang lomba ' . $competition->name . ' berhasil ditambahkan.');
    }

    public function updateCompetition(Request $request, $id)
    {
        $competition = Competition::withoutGlobalScope('order')->findOrFail($id);

        $isMultiTier = in_array($competition->code, ['BLT', 'MTQ', 'POP', 'TMJ']);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:competitions,code,' . $competition->id],
            'type' => ['required', 'in:individu,tim,kelompok,regu,kolektif'],
            'min_members' => ['required', 'integer', 'min:1'],
            'max_members' => ['required', 'integer', 'min:1'],
            'quota' => [$isMultiTier ? 'nullable' : 'required', 'integer', 'min:0'],
            'registration_fee' => ['nullable', 'numeric', 'min:0'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'status' => [$isMultiTier ? 'nullable' : 'required', 'in:buka,tutup,selesai'],
            'venue' => ['nullable', 'string'],
            'schedule_date' => ['nullable', 'date'],
            'schedule_time' => ['nullable', 'string'],
            'rules' => ['nullable', 'string'],
            'guidelines_file' => ['nullable', 'string'],
            'guidelines_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'order' => ['nullable', 'integer'],
        ]);

        $guidelinesPath = $competition->guidelines_file;
        if ($request->hasFile('guidelines_pdf')) {
            $guidelinesPath = $request->file('guidelines_pdf')->store('guidelines', 'public');
        } elseif ($request->has('guidelines_file')) {
            $guidelinesPath = $request->input('guidelines_file');
        }

        $competition->update([
            'category_id' => $validated['category_id'],
            'pic_id' => $validated['pic_id'] ?? $competition->pic_id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'min_members' => $validated['min_members'],
            'max_members' => $validated['max_members'],
            'quota' => $validated['quota'] ?? $competition->quota ?? 10,
            'registration_fee' => $validated['registration_fee'] ?? $competition->registration_fee ?? 0,
            'status' => $validated['status'] ?? $competition->status ?? 'buka',
            'venue' => $validated['venue'] ?? null,
            'schedule_date' => $validated['schedule_date'] ?? null,
            'schedule_time' => $validated['schedule_time'] ?? null,
            'rules' => $validated['rules'] ?? null,
            'guidelines_file' => $guidelinesPath,
            'order' => isset($validated['order']) ? (int) $validated['order'] : ($competition->order ?? 0),
            'show_criteria' => $request->has('show_criteria') ? $request->boolean('show_criteria') : true,
            'is_live_score' => $request->has('is_live_score') ? $request->boolean('is_live_score') : $competition->is_live_score,
        ]);

        // Update Criteria if submitted
        if ($request->has('criteria') && is_array($request->criteria)) {
            $competition->criteria()->delete();
            foreach ($request->criteria as $crit) {
                if (!empty($crit['name'])) {
                    CompetitionCriterion::create([
                        'competition_id' => $competition->id,
                        'name' => $crit['name'],
                        'weight_percentage' => (int) ($crit['weight_percentage'] ?? 100),
                        'min_score' => (float) ($crit['min_score'] ?? 0),
                        'max_score' => (float) ($crit['max_score'] ?? 100),
                        'description' => $crit['description'] ?? null,
                    ]);
                }
            }
        }

        if ($competition->code === 'BLT') {
            // Fees - Tunggal PA
            if ($request->filled('blt_fee_a_tunggal_pa')) AppSetting::set('blt_fee_a_tunggal_pa', (float) $request->input('blt_fee_a_tunggal_pa'), 'pricing');
            if ($request->filled('blt_fee_b_tunggal_pa')) AppSetting::set('blt_fee_b_tunggal_pa', (float) $request->input('blt_fee_b_tunggal_pa'), 'pricing');
            if ($request->filled('blt_fee_c_tunggal_pa')) AppSetting::set('blt_fee_c_tunggal_pa', (float) $request->input('blt_fee_c_tunggal_pa'), 'pricing');

            // Fees - Tunggal PI
            if ($request->filled('blt_fee_a_tunggal_pi')) AppSetting::set('blt_fee_a_tunggal_pi', (float) $request->input('blt_fee_a_tunggal_pi'), 'pricing');
            if ($request->filled('blt_fee_b_tunggal_pi')) AppSetting::set('blt_fee_b_tunggal_pi', (float) $request->input('blt_fee_b_tunggal_pi'), 'pricing');
            if ($request->filled('blt_fee_c_tunggal_pi')) AppSetting::set('blt_fee_c_tunggal_pi', (float) $request->input('blt_fee_c_tunggal_pi'), 'pricing');

            // Fees - Ganda PA & PI
            if ($request->filled('blt_fee_ganda_pa')) AppSetting::set('blt_fee_ganda_pa', (float) $request->input('blt_fee_ganda_pa'), 'pricing');
            if ($request->filled('blt_fee_ganda_pi')) AppSetting::set('blt_fee_ganda_pi', (float) $request->input('blt_fee_ganda_pi'), 'pricing');

            // Quotas - Tunggal PA
            if ($request->filled('blt_quota_a_tunggal_pa')) AppSetting::set('blt_quota_a_tunggal_pa', (int) $request->input('blt_quota_a_tunggal_pa'), 'pricing');
            if ($request->filled('blt_quota_b_tunggal_pa')) AppSetting::set('blt_quota_b_tunggal_pa', (int) $request->input('blt_quota_b_tunggal_pa'), 'pricing');
            if ($request->filled('blt_quota_c_tunggal_pa')) AppSetting::set('blt_quota_c_tunggal_pa', (int) $request->input('blt_quota_c_tunggal_pa'), 'pricing');

            // Quotas - Tunggal PI
            if ($request->filled('blt_quota_a_tunggal_pi')) AppSetting::set('blt_quota_a_tunggal_pi', (int) $request->input('blt_quota_a_tunggal_pi'), 'pricing');
            if ($request->filled('blt_quota_b_tunggal_pi')) AppSetting::set('blt_quota_b_tunggal_pi', (int) $request->input('blt_quota_b_tunggal_pi'), 'pricing');
            if ($request->filled('blt_quota_c_tunggal_pi')) AppSetting::set('blt_quota_c_tunggal_pi', (int) $request->input('blt_quota_c_tunggal_pi'), 'pricing');

            // Quotas - Ganda PA & PI
            if ($request->filled('blt_quota_ganda_pa')) AppSetting::set('blt_quota_ganda_pa', (int) $request->input('blt_quota_ganda_pa'), 'pricing');
            if ($request->filled('blt_quota_ganda_pi')) AppSetting::set('blt_quota_ganda_pi', (int) $request->input('blt_quota_ganda_pi'), 'pricing');

            // PICs - per Sektor
            if ($request->has('blt_pic_tunggal_pa')) AppSetting::set('blt_pic_tunggal_pa', $request->input('blt_pic_tunggal_pa') ?: null, 'general');
            if ($request->has('blt_pic_tunggal_pi')) AppSetting::set('blt_pic_tunggal_pi', $request->input('blt_pic_tunggal_pi') ?: null, 'general');
            if ($request->has('blt_pic_ganda_pa')) AppSetting::set('blt_pic_ganda_pa', $request->input('blt_pic_ganda_pa') ?: null, 'general');
            if ($request->has('blt_pic_ganda_pi')) AppSetting::set('blt_pic_ganda_pi', $request->input('blt_pic_ganda_pi') ?: null, 'general');

            // Status - per Kategori & Sektor
            if ($request->has('blt_status_a_tunggal_pa')) AppSetting::set('blt_status_a_tunggal_pa', $request->input('blt_status_a_tunggal_pa') ?: 'buka', 'general');
            if ($request->has('blt_status_b_tunggal_pa')) AppSetting::set('blt_status_b_tunggal_pa', $request->input('blt_status_b_tunggal_pa') ?: 'buka', 'general');
            if ($request->has('blt_status_c_tunggal_pa')) AppSetting::set('blt_status_c_tunggal_pa', $request->input('blt_status_c_tunggal_pa') ?: 'buka', 'general');

            if ($request->has('blt_status_a_tunggal_pi')) AppSetting::set('blt_status_a_tunggal_pi', $request->input('blt_status_a_tunggal_pi') ?: 'buka', 'general');
            if ($request->has('blt_status_b_tunggal_pi')) AppSetting::set('blt_status_b_tunggal_pi', $request->input('blt_status_b_tunggal_pi') ?: 'buka', 'general');
            if ($request->has('blt_status_c_tunggal_pi')) AppSetting::set('blt_status_c_tunggal_pi', $request->input('blt_status_c_tunggal_pi') ?: 'buka', 'general');

            if ($request->has('blt_status_ganda_pa')) AppSetting::set('blt_status_ganda_pa', $request->input('blt_status_ganda_pa') ?: 'buka', 'general');
            if ($request->has('blt_status_ganda_pi')) AppSetting::set('blt_status_ganda_pi', $request->input('blt_status_ganda_pi') ?: 'buka', 'general');
            if ($request->has('blt_status_tunggal_pa')) AppSetting::set('blt_status_tunggal_pa', $request->input('blt_status_tunggal_pa') ?: 'buka', 'general');
            if ($request->has('blt_status_tunggal_pi')) AppSetting::set('blt_status_tunggal_pi', $request->input('blt_status_tunggal_pi') ?: 'buka', 'general');
        }

        // Khusus Cabang MTQ & Pop Singer (Sektor Individu PA & PI)
        if (in_array($competition->code, ['MTQ', 'POP'])) {
            $prefix = strtolower($competition->code); // 'mtq' or 'pop'
            
            // Biaya PA & PI
            if ($request->filled($prefix . '_fee_pa')) AppSetting::set($prefix . '_fee_pa', (float) $request->input($prefix . '_fee_pa'), 'pricing');
            if ($request->filled($prefix . '_fee_pi')) AppSetting::set($prefix . '_fee_pi', (float) $request->input($prefix . '_fee_pi'), 'pricing');

            // Kuota PA & PI
            if ($request->filled($prefix . '_quota_pa')) AppSetting::set($prefix . '_quota_pa', (int) $request->input($prefix . '_quota_pa'), 'pricing');
            if ($request->filled($prefix . '_quota_pi')) AppSetting::set($prefix . '_quota_pi', (int) $request->input($prefix . '_quota_pi'), 'pricing');

            // PIC PA & PI
            if ($request->has($prefix . '_pic_pa')) AppSetting::set($prefix . '_pic_pa', $request->input($prefix . '_pic_pa') ?: null, 'general');
            if ($request->has($prefix . '_pic_pi')) AppSetting::set($prefix . '_pic_pi', $request->input($prefix . '_pic_pi') ?: null, 'general');

            // Status PA & PI
            if ($request->has($prefix . '_status_pa')) AppSetting::set($prefix . '_status_pa', $request->input($prefix . '_status_pa') ?: 'buka', 'general');
            if ($request->has($prefix . '_status_pi')) AppSetting::set($prefix . '_status_pi', $request->input($prefix . '_status_pi') ?: 'buka', 'general');
        }

        // Khusus Cabang Tenis Meja (TMJ - Kat A & Kat B)
        if ($competition->code === 'TMJ') {
            // Fees - Tunggal PA (Kat A & B)
            if ($request->filled('tmj_fee_a_tunggal_pa')) AppSetting::set('tmj_fee_a_tunggal_pa', (float) $request->input('tmj_fee_a_tunggal_pa'), 'pricing');
            if ($request->filled('tmj_fee_b_tunggal_pa')) AppSetting::set('tmj_fee_b_tunggal_pa', (float) $request->input('tmj_fee_b_tunggal_pa'), 'pricing');

            // Fees - Tunggal PI (Kat A & B)
            if ($request->filled('tmj_fee_a_tunggal_pi')) AppSetting::set('tmj_fee_a_tunggal_pi', (float) $request->input('tmj_fee_a_tunggal_pi'), 'pricing');
            if ($request->filled('tmj_fee_b_tunggal_pi')) AppSetting::set('tmj_fee_b_tunggal_pi', (float) $request->input('tmj_fee_b_tunggal_pi'), 'pricing');

            // Quotas - Tunggal PA (Kat A & B)
            if ($request->filled('tmj_quota_a_tunggal_pa')) AppSetting::set('tmj_quota_a_tunggal_pa', (int) $request->input('tmj_quota_a_tunggal_pa'), 'pricing');
            if ($request->filled('tmj_quota_b_tunggal_pa')) AppSetting::set('tmj_quota_b_tunggal_pa', (int) $request->input('tmj_quota_b_tunggal_pa'), 'pricing');

            // Quotas - Tunggal PI (Kat A & B)
            if ($request->filled('tmj_quota_a_tunggal_pi')) AppSetting::set('tmj_quota_a_tunggal_pi', (int) $request->input('tmj_quota_a_tunggal_pi'), 'pricing');
            if ($request->filled('tmj_quota_b_tunggal_pi')) AppSetting::set('tmj_quota_b_tunggal_pi', (int) $request->input('tmj_quota_b_tunggal_pi'), 'pricing');

            // PICs - per Sektor
            if ($request->has('tmj_pic_tunggal_pa')) AppSetting::set('tmj_pic_tunggal_pa', $request->input('tmj_pic_tunggal_pa') ?: null, 'general');
            if ($request->has('tmj_pic_tunggal_pi')) AppSetting::set('tmj_pic_tunggal_pi', $request->input('tmj_pic_tunggal_pi') ?: null, 'general');

            // Status - per Kategori & Sektor
            if ($request->has('tmj_status_a_tunggal_pa')) AppSetting::set('tmj_status_a_tunggal_pa', $request->input('tmj_status_a_tunggal_pa') ?: 'buka', 'general');
            if ($request->has('tmj_status_b_tunggal_pa')) AppSetting::set('tmj_status_b_tunggal_pa', $request->input('tmj_status_b_tunggal_pa') ?: 'buka', 'general');
            if ($request->has('tmj_status_a_tunggal_pi')) AppSetting::set('tmj_status_a_tunggal_pi', $request->input('tmj_status_a_tunggal_pi') ?: 'buka', 'general');
            if ($request->has('tmj_status_b_tunggal_pi')) AppSetting::set('tmj_status_b_tunggal_pi', $request->input('tmj_status_b_tunggal_pi') ?: 'buka', 'general');
            if ($request->has('tmj_status_tunggal_pa')) AppSetting::set('tmj_status_tunggal_pa', $request->input('tmj_status_tunggal_pa') ?: 'buka', 'general');
            if ($request->has('tmj_status_tunggal_pi')) AppSetting::set('tmj_status_tunggal_pi', $request->input('tmj_status_tunggal_pi') ?: 'buka', 'general');
        }

        return redirect()->route('admin.competitions')->with('success', 'Cabang lomba ' . $competition->name . ' berhasil diperbarui.');
    }

    public function deleteCompetition($id)
    {
        $competition = Competition::findOrFail($id);
        $name = $competition->name;
        $competition->delete();

        return redirect()->route('admin.competitions')->with('success', 'Cabang lomba ' . $name . ' berhasil dihapus.');
    }

    /**
     * Category Management
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_group' => 'required|in:akademik,non_akademik',
            'icon' => 'nullable|string|max:50',
            'order' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $slug = Str::slug($validated['name']);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-' . (Category::max('id') + 1);
        }

        Category::create([
            'name' => $validated['name'],
            'category_group' => $validated['category_group'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?? 'trophy',
            'order' => $validated['order'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.competitions', ['tab' => 'kategori'])->with('success', 'Jenis Lomba ' . $validated['name'] . ' berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_group' => 'required|in:akademik,non_akademik',
            'icon' => 'nullable|string|max:50',
            'order' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $slug = Str::slug($validated['name']);
        if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug .= '-' . $category->id;
        }

        $category->update([
            'name' => $validated['name'],
            'category_group' => $validated['category_group'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?? 'trophy',
            'order' => $validated['order'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.competitions', ['tab' => 'kategori'])->with('success', 'Kategori ' . $category->name . ' berhasil diperbarui.');
    }

    public function deleteCategory($id)
    {
        $category = Category::withCount('competitions')->findOrFail($id);

        if ($category->competitions_count > 0) {
            return redirect()->route('admin.competitions', ['tab' => 'kategori'])->with('error', 'Kategori ' . $category->name . ' tidak dapat dihapus karena masih memuat ' . $category->competitions_count . ' cabang lomba.');
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.competitions', ['tab' => 'kategori'])->with('success', 'Kategori ' . $name . ' berhasil dihapus.');
    }

    /**
     * Timeline Management
     */
    public function storeTimeline(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date_label' => 'required|string|max:255',
            'time_label' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
            'is_active' => 'nullable|boolean',
        ]);

        Timeline::create([
            'title' => $validated['title'],
            'date_label' => $validated['date_label'],
            'time_label' => $validated['time_label'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.competitions', ['tab' => 'timeline'])->with('success', 'Jadwal rangkaian acara berhasil ditambahkan.');
    }

    public function updateTimeline(Request $request, $id)
    {
        $timeline = Timeline::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date_label' => 'required|string|max:255',
            'time_label' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $timeline->update([
            'title' => $validated['title'],
            'date_label' => $validated['date_label'],
            'time_label' => $validated['time_label'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.competitions', ['tab' => 'timeline'])->with('success', 'Jadwal rangkaian acara berhasil diperbarui.');
    }

    public function deleteTimeline($id)
    {
        $timeline = Timeline::findOrFail($id);
        $timeline->delete();

        return redirect()->route('admin.competitions', ['tab' => 'timeline'])->with('success', 'Jadwal tahapan berhasil dihapus.');
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('institution_name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'role' => ['required', 'in:superadmin,pic_lomba,juri,peserta'],
            'status' => ['nullable', 'in:active,inactive'],
            'phone' => ['nullable', 'string', 'max:50'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'institution_name' => $validated['institution_name'] ?? null,
            'position' => $validated['position'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return redirect()->route('admin.users')->with('success', 'Pengguna baru ' . $validated['name'] . ' berhasil ditambahkan.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:superadmin,pic_lomba,juri,peserta'],
            'status' => ['required', 'in:active,inactive'],
            'phone' => ['nullable', 'string', 'max:50'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'phone' => $validated['phone'] ?? null,
            'institution_name' => $validated['institution_name'] ?? null,
            'position' => $validated['position'] ?? null,
        ]);

        return redirect()->route('admin.users')->with('success', 'Data pengguna ' . $user->name . ' berhasil diperbarui.');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Password akun ' . $user->name . ' (' . $user->email . ') berhasil direset menjadi: ' . $validated['password']);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Akun pengguna "' . $name . '" berhasil dihapus.');
    }

    public function recap()
    {
        // 1. Fetch all competitions with full registration details & scores
        $competitions = Competition::with([
            'category',
            'pic',
            'registrations' => function ($q) {
                $q->with(['members', 'scores.details.criterion', 'user', 'invoice']);
            }
        ])->get();

        // 2. Tab 1: Financial & Quota Recap per Competition
        $financeRecap = [];
        $grandTotals = [
            'total_quota' => 0,
            'total_registrations' => 0,
            'verified_registrations' => 0,
            'pending_registrations' => 0,
            'rejected_registrations' => 0,
            'verified_income' => 0,
            'pending_income' => 0,
            'total_potential_income' => 0,
        ];

        foreach ($competitions as $comp) {
            $totalRegs = $comp->registrations->count();
            $verifiedRegs = $comp->registrations->where('status', 'verified');
            $pendingRegs = $comp->registrations->where('status', 'pending');
            $rejectedRegs = $comp->registrations->where('status', 'rejected');

            $verifiedIncome = $verifiedRegs->sum(fn($r) => $r->fee);
            $pendingIncome = $pendingRegs->sum(fn($r) => $r->fee);
            $totalIncome = $verifiedIncome + $pendingIncome;

            $financeRecap[] = [
                'competition' => $comp,
                'quota' => $comp->quota,
                'total_regs' => $totalRegs,
                'verified_count' => $verifiedRegs->count(),
                'pending_count' => $pendingRegs->count(),
                'rejected_count' => $rejectedRegs->count(),
                'verified_income' => $verifiedIncome,
                'pending_income' => $pendingIncome,
                'total_income' => $totalIncome,
            ];

            $grandTotals['total_quota'] += $comp->quota;
            $grandTotals['total_registrations'] += $totalRegs;
            $grandTotals['verified_registrations'] += $verifiedRegs->count();
            $grandTotals['pending_registrations'] += $pendingRegs->count();
            $grandTotals['rejected_registrations'] += $rejectedRegs->count();
            $grandTotals['verified_income'] += $verifiedIncome;
            $grandTotals['pending_income'] += $pendingIncome;
            $grandTotals['total_potential_income'] += $totalIncome;
        }

        // 3. Tab 2: Master All Registrations
        $allRegistrations = Registration::with(['competition.category', 'members', 'user', 'invoice'])
            ->latest()
            ->get();

        // 4. Tab 3: Winners Recap per Competition
        $winnersByCompetition = [];
        $institutionScores = [];

        foreach ($competitions as $comp) {
            $ranked = $comp->registrations->where('status', 'verified')->map(function ($reg) {
                $lockedScores = $reg->scores->where('is_locked', true);
                $avg = $lockedScores->isNotEmpty() ? $lockedScores->avg('total_score') : 0;
                return [
                    'registration' => $reg,
                    'institution' => $reg->institution_name,
                    'avg' => $avg,
                ];
            })->where('avg', '>', 0)->sortByDesc('avg')->values();

            $winnersByCompetition[] = [
                'competition' => $comp,
                'juara_1' => $ranked[0] ?? null,
                'juara_2' => $ranked[1] ?? null,
                'juara_3' => $ranked[2] ?? null,
                'harapan_1' => $ranked[3] ?? null,
                'total_participants' => $comp->registrations->where('status', 'verified')->count(),
                'has_results' => isset($ranked[0]),
            ];

            // Tab 4: Standings Points Calculation
            if (isset($ranked[0])) {
                $inst = $ranked[0]['institution'];
                $institutionScores[$inst]['emas'] = ($institutionScores[$inst]['emas'] ?? 0) + 1;
                $institutionScores[$inst]['poin'] = ($institutionScores[$inst]['poin'] ?? 0) + 5;
            }
            if (isset($ranked[1])) {
                $inst = $ranked[1]['institution'];
                $institutionScores[$inst]['perak'] = ($institutionScores[$inst]['perak'] ?? 0) + 1;
                $institutionScores[$inst]['poin'] = ($institutionScores[$inst]['poin'] ?? 0) + 3;
            }
            if (isset($ranked[2])) {
                $inst = $ranked[2]['institution'];
                $institutionScores[$inst]['perunggu'] = ($institutionScores[$inst]['perunggu'] ?? 0) + 1;
                $institutionScores[$inst]['poin'] = ($institutionScores[$inst]['poin'] ?? 0) + 1;
            }
        }

        // 5. Tab 4: Standings Juara Umum
        $standings = collect($institutionScores)->map(function ($val, $key) {
            return [
                'institution' => $key,
                'emas' => $val['emas'] ?? 0,
                'perak' => $val['perak'] ?? 0,
                'perunggu' => $val['perunggu'] ?? 0,
                'total_medali' => ($val['emas'] ?? 0) + ($val['perak'] ?? 0) + ($val['perunggu'] ?? 0),
                'total_poin' => $val['poin'] ?? 0,
            ];
        })->sortByDesc('total_poin')->values();

        return view('admin.recap', compact(
            'competitions',
            'financeRecap',
            'grandTotals',
            'allRegistrations',
            'winnersByCompetition',
            'standings'
        ));
    }

    public function juriWasitUndian(Request $request)
    {
        $activeTab = $request->query('tab', 'juri');

        // 1. Data for Tab 1: Penilaian Dewan Juri
        $competitionsWithJudging = Competition::with(['category', 'criteria', 'pic', 'judges', 'registrations.scores'])
            ->get()
            ->map(function ($comp) {
                $verified = $comp->registrations->where('status', 'verified');
                $scoredCount = $verified->filter(function ($reg) {
                    return $reg->scores->where('is_locked', true)->isNotEmpty();
                })->count();

                return [
                    'id' => $comp->id,
                    'code' => $comp->code,
                    'name' => $comp->name,
                    'slug' => $comp->slug,
                    'category' => $comp->category->name ?? '-',
                    'venue' => $comp->venue ?? '-',
                    'schedule_date' => $comp->schedule_date,
                    'schedule_time' => $comp->schedule_time,
                    'total_verified' => $verified->count(),
                    'total_scored' => $scoredCount,
                    'is_scoring_complete' => ($verified->count() > 0 && $scoredCount >= $verified->count()),
                    'is_live_score' => (bool) $comp->is_live_score,
                    'criteria_count' => $comp->criteria->count(),
                    'criteria' => $comp->criteria,
                    'judges' => $comp->judges,
                ];
            });

        // 2. Data for Tab 2: Wasit & Pertandingan Bulu Tangkis
        $matchQuery = \App\Models\BadmintonMatch::with(['competition', 'umpire'])->latest();
        if ($request->filled('court')) {
            $matchQuery->where('court_number', $request->court);
        }
        if ($request->filled('match_status')) {
            $matchQuery->where('match_status', $request->match_status);
        }
        if ($request->filled('category')) {
            $matchQuery->where('category', $request->category);
        }
        $badmintonMatches = $matchQuery->paginate(12)->appends($request->query());

        $badmintonCompetitions = Competition::where('code', 'BLT')
            ->orWhere('name', 'like', '%Bulu Tangkis%')
            ->orWhere('name', 'like', '%Badminton%')
            ->get();
        if ($badmintonCompetitions->isEmpty()) {
            $badmintonCompetitions = Competition::all();
        }

        $badmintonCourts = \App\Models\BadmintonMatch::select('court_number')->distinct()->pluck('court_number');
        if ($badmintonCourts->isEmpty()) {
            $badmintonCourts = collect(['Lapangan 1', 'Lapangan 2', 'Lapangan 3']);
        }

        // 3. Data for Tab 3: Undian Nomor Peserta
        $drawCompetitions = Competition::with(['category', 'registrations' => function ($q) {
                $q->with('members');
            }])
            ->get()
            ->map(function ($comp) {
                $verified = $comp->registrations->where('status', 'verified');
                $drawnCount = $verified->whereNotNull('draw_number')->count();
                $undrawnCount = $verified->whereNull('draw_number')->count();
                $totalVerified = $verified->count();

                return [
                    'id' => $comp->id,
                    'code' => $comp->code,
                    'name' => $comp->name,
                    'slug' => $comp->slug,
                    'category' => $comp->category->name ?? '-',
                    'total_registrations' => $comp->registrations->count(),
                    'total_verified' => $totalVerified,
                    'drawn_count' => $drawnCount,
                    'undrawn_count' => $undrawnCount,
                    'is_complete' => ($totalVerified > 0 && $undrawnCount === 0),
                ];
            });

        $totalDrawn = $drawCompetitions->sum('drawn_count');
        $totalUndrawn = $drawCompetitions->sum('undrawn_count');
        $totalVerifiedAll = $drawCompetitions->sum('total_verified');

        return view('admin.juri-wasit', compact(
            'activeTab',
            'competitionsWithJudging',
            'badmintonMatches',
            'badmintonCompetitions',
            'badmintonCourts',
            'drawCompetitions',
            'totalDrawn',
            'totalUndrawn',
            'totalVerifiedAll'
        ));
    }

    public function toggleLiveScore(Request $request, $id)
    {
        $competition = Competition::findOrFail($id);
        $competition->is_live_score = !$competition->is_live_score;
        $competition->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_live_score' => (bool) $competition->is_live_score,
                'message' => 'Status Live Score ' . $competition->name . ' berhasil diubah ke ' . ($competition->is_live_score ? 'PUBLIK (AKTIF)' : 'RAHASIA (OFF)') . '.',
            ]);
        }

        return back()->with('success', 'Status Live Score untuk ' . $competition->name . ' berhasil diperbarui.');
    }
}
