<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\CertificateTemplate;
use App\Models\Competition;
use App\Models\Registration;
use App\Services\QrSignatureService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Display Certificate & Piagam Management Dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Scope competitions based on role
        if ($user->role === 'superadmin') {
            $competitions = Competition::with(['category', 'judges', 'pic'])->get();
        } elseif ($user->role === 'pic_lomba') {
            $managedIds = PicController::getManagedCompetitionIds($user);
            $competitions = Competition::with(['category', 'judges', 'pic'])->whereIn('id', $managedIds)->get();
        } else {
            $competitions = Competition::with(['category', 'judges', 'pic'])->get();
        }

        $selectedCompId = $request->query('competition_id', $competitions->first()?->id);
        $selectedComp = $competitions->firstWhere('id', $selectedCompId) ?: $competitions->first();

        $type = $request->query('type', 'juara'); // 'juara', 'peserta', 'pembimbing', 'juri'
        if (! in_array($type, ['juara', 'peserta', 'pembimbing', 'juri'])) {
            $type = 'juara';
        }

        // Fetch template for this type & competition (or fallback to general default for this type)
        $template = null;
        if ($selectedComp) {
            $template = CertificateTemplate::where('type', $type)
                ->where('competition_id', $selectedComp->id)
                ->where('is_active', true)
                ->first();
        }

        if (! $template) {
            $template = CertificateTemplate::where('type', $type)
                ->whereNull('competition_id')
                ->where('is_active', true)
                ->first();
        }

        // If no template exists in database, instantiate an in-memory default
        if (! $template) {
            $template = new CertificateTemplate([
                'name' => 'Default '.ucfirst($type),
                'type' => $type,
                'competition_id' => null,
                'layout_config' => CertificateTemplate::defaultLayoutConfig(),
                'number_format' => '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]',
                'is_active' => true,
            ]);
        }

        // Fetch recipient list according to type
        $recipients = collect();
        $sectorsData = [];
        $isReleased = false;

        if ($selectedComp) {
            $releaseSetting = AppSetting::where('key', 'certificate_release_'.$selectedComp->id)->first();
            $isReleased = $releaseSetting && $releaseSetting->value === '1';

            if ($type === 'juara') {
                $sectorsDef = OfficialReportController::getCompetitionSectors($selectedComp);
                $regs = $selectedComp->registrations()
                    ->where('status', 'verified')
                    ->with(['members', 'scores.details'])
                    ->get();

                foreach ($sectorsDef as $secKey => $secDef) {
                    $filteredRegs = $regs->filter(function ($r) use ($secDef, $selectedComp) {
                        $isGanda = $r->isGanda();
                        $gender = $r->primary_gender;

                        if ($secDef['is_ganda'] && ! $isGanda) {
                            return false;
                        }
                        if (! $secDef['is_ganda'] && $isGanda && in_array($selectedComp->code, ['BLT', 'TMJ'])) {
                            return false;
                        }
                        if ($secDef['gender'] !== 'all' && $gender !== $secDef['gender']) {
                            return false;
                        }

                        if ($secDef['kat'] === 'a') {
                            return $r->isKatA();
                        } elseif ($secDef['kat'] === 'b') {
                            return $r->isKatB();
                        } elseif ($secDef['kat'] === 'c') {
                            return $r->isKatC();
                        }

                        return true;
                    });

                    $ranked = $filteredRegs->map(function ($r) {
                        $lockedScores = $r->scores->where('is_locked', true);
                        $avgScore = $lockedScores->isNotEmpty() ? round($lockedScores->avg('total_score'), 2) : 0;
                        $participantName = $r->pure_name ?: ($r->team_name ?: ($r->members->first()?->full_name ?? ('Peserta #'.$r->id)));
                        $schoolName = $r->display_school ?: ($r->institution_name ?: '-');

                        return [
                            'registration' => $r,
                            'participant_number' => $r->participant_number ?: $r->registration_code,
                            'display_name' => $participantName,
                            'institution_name' => $schoolName,
                            'score' => $avgScore > 0 ? $avgScore : '',
                            'has_score' => ($lockedScores->isNotEmpty() && $avgScore > 0),
                        ];
                    })
                        ->filter(fn ($item) => $item['has_score'])
                        ->sortByDesc(fn ($item) => (float) $item['score'])->values();

                    $tierLabels = [
                        'Juara 1',
                        'Juara 2',
                        'Juara 3',
                        'Juara Harapan 1',
                        'Juara Harapan 2',
                        'Juara Harapan 3',
                    ];

                    $sectorWinners = [];
                    foreach ($tierLabels as $idx => $label) {
                        if (isset($ranked[$idx])) {
                            $sectorWinners[] = [
                                'tier_label' => $label,
                                'winner' => $ranked[$idx],
                            ];
                        }
                    }

                    $sectorsData[$secKey] = [
                        'definition' => $secDef,
                        'winners' => $sectorWinners,
                    ];
                }
            } elseif ($type === 'peserta') {
                $recipients = $selectedComp->registrations()
                    ->where('status', 'verified')
                    ->with(['members', 'user'])
                    ->orderBy('participant_number', 'asc')
                    ->get()
                    ->map(function ($r) use ($selectedComp) {
                        return [
                            'id' => $r->id,
                            'registration' => $r,
                            'participant_number' => $r->participant_number ?: $r->registration_code,
                            'name' => $r->pure_name ?: ($r->team_name ?: ($r->members->first()?->full_name ?? ('Peserta #'.$r->id))),
                            'institution' => $r->display_school ?: ($r->institution_name ?: '-'),
                            'role_label' => 'Sebagai Peserta',
                            'competition_name' => $selectedComp->name,
                        ];
                    });
            } elseif ($type === 'pembimbing') {
                $regs = $selectedComp->registrations()
                    ->where('status', 'verified')
                    ->whereNotNull('official_name')
                    ->where('official_name', '!=', '')
                    ->get();

                $uniqueOfficials = [];
                foreach ($regs as $r) {
                    $key = Str::slug($r->official_name.'-'.($r->display_school ?: $r->institution_name));
                    if (! isset($uniqueOfficials[$key])) {
                        $uniqueOfficials[$key] = [
                            'id' => $r->id,
                            'registration' => $r,
                            'participant_number' => '-',
                            'name' => $r->official_name,
                            'institution' => $r->display_school ?: ($r->institution_name ?: '-'),
                            'phone' => $r->official_phone ?: '-',
                            'role_label' => 'Sebagai Guru Pembimbing / Pendamping',
                            'competition_name' => $selectedComp->name,
                        ];
                    }
                }
                $recipients = collect(array_values($uniqueOfficials));
            } elseif ($type === 'juri') {
                $judges = $selectedComp->judges;
                $recipients = $judges->map(function ($j) use ($selectedComp) {
                    return [
                        'id' => $j->id,
                        'participant_number' => '-',
                        'name' => $j->name,
                        'institution' => $j->institution ?? 'Dewan Juri / Wasit TALENTA',
                        'role_label' => 'Sebagai Dewan Juri / Wasit',
                        'competition_name' => $selectedComp->name,
                    ];
                });
            }
        }

        $allTemplates = CertificateTemplate::with('competition')->get();
        $nowDate = Carbon::now();
        $dateSpelled = OfficialReportController::getDateSpelledOut($nowDate);

        return view('admin.certificates.index', compact(
            'competitions',
            'selectedComp',
            'type',
            'template',
            'allTemplates',
            'recipients',
            'sectorsData',
            'isReleased',
            'dateSpelled'
        ));
    }

    /**
     * Visual Coordinate Designer Page with Live Preview
     */
    public function designer(Request $request, $id = null)
    {
        $template = null;
        if ($id) {
            $template = CertificateTemplate::findOrFail($id);
        } else {
            $type = $request->query('type', 'juara');
            $compId = $request->query('competition_id');

            $query = CertificateTemplate::where('type', $type);
            if ($compId) {
                $query->where('competition_id', $compId);
            } else {
                $query->whereNull('competition_id');
            }
            $template = $query->first();

            if (! $template) {
                $template = CertificateTemplate::create([
                    'name' => 'Template '.ucfirst($type).($compId ? ' Khusus Cabang' : ' Umum'),
                    'type' => $type,
                    'competition_id' => $compId ?: null,
                    'layout_config' => CertificateTemplate::defaultLayoutConfig(),
                    'number_format' => '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]',
                    'is_active' => true,
                ]);
            }
        }

        $competitions = Competition::orderBy('name')->get();

        return view('admin.certificates.designer', compact('template', 'competitions'));
    }

    /**
     * Store or Update Template with Background Image Upload
     */
    public function storeOrUpdateTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:juara,peserta,pembimbing,juri',
            'competition_id' => 'nullable|exists:competitions,id',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // max 10MB
            'number_format' => 'nullable|string|max:255',
        ]);

        $templateId = $request->input('template_id');
        $template = $templateId ? CertificateTemplate::findOrFail($templateId) : new CertificateTemplate;

        $template->name = $request->input('name');
        $template->type = $request->input('type');
        $template->competition_id = $request->input('competition_id') ?: null;
        $template->number_format = $request->input('number_format') ?: '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]';

        if ($request->hasFile('background_image')) {
            $file = $request->file('background_image');
            $fileName = 'cert_tpl_'.time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();

            // Store in certificates folder inside public storage disk
            $storedPath = $file->storeAs('certificates', $fileName, 'public');

            // Sync with public storage if helper available
            if (class_exists(AdminSettingsController::class)) {
                AdminSettingsController::ensurePublicStorageSync($storedPath);
            }

            // Remove old image if exists
            if ($template->background_path && Storage::disk('public')->exists($template->background_path)) {
                Storage::disk('public')->delete($template->background_path);
            }

            $template->background_path = $storedPath;
        }

        if (! $template->layout_config) {
            $template->layout_config = CertificateTemplate::defaultLayoutConfig();
        }

        $template->save();

        return redirect()->route('admin.certificates.designer', ['id' => $template->id])
            ->with('success', 'Template sertifikat berhasil disimpan! Silakan atur posisi teks pada panel di bawah.');
    }

    /**
     * Save Layout Configuration via AJAX
     */
    public function saveLayout(Request $request, $id)
    {
        $template = CertificateTemplate::findOrFail($id);
        $layoutConfig = $request->input('layout_config');

        if (is_string($layoutConfig)) {
            $layoutConfig = json_decode($layoutConfig, true);
        }

        if (is_array($layoutConfig)) {
            $template->layout_config = $layoutConfig;
            $template->save();

            return response()->json([
                'success' => true,
                'message' => 'Tata letak dan koordinat teks berhasil disimpan!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Format konfigurasi layout tidak valid.',
        ], 422);
    }

    /**
     * Delete a template
     */
    public function deleteTemplate($id)
    {
        $template = CertificateTemplate::findOrFail($id);

        if ($template->background_path && Storage::disk('public')->exists($template->background_path)) {
            Storage::disk('public')->delete($template->background_path);
        }

        $template->delete();

        return redirect()->route('admin.certificates.index')
            ->with('success', 'Template sertifikat berhasil dihapus.');
    }

    /**
     * Toggle Certificate Release for Participants
     */
    public function toggleRelease(Request $request)
    {
        $competitionId = $request->input('competition_id');
        $competition = Competition::findOrFail($competitionId);

        $key = 'certificate_release_'.$competition->id;
        $setting = AppSetting::firstOrNew(['key' => $key]);
        $newValue = ($setting->value === '1') ? '0' : '1';
        $setting->value = $newValue;
        $setting->group = 'certificate';
        $setting->save();

        $statusMsg = $newValue === '1' ? 'dibuka untuk diunduh mandiri oleh peserta' : 'ditutup kembali';

        return redirect()->back()->with('success', "Akses sertifikat untuk cabang {$competition->name} berhasil {$statusMsg}.");
    }

    /**
     * Generate Certificate Code for Verification
     */
    protected function generateCertCode(string $type, $identifier, ?Competition $comp): string
    {
        $compCode = strtoupper($comp->code ?? 'TLT');
        $typePrefix = match ($type) {
            'juara' => 'JRA',
            'peserta' => 'PST',
            'pembimbing' => 'PMB',
            'juri' => 'JRI',
            default => 'CRT',
        };

        return 'TLT-'.$typePrefix.'-'.$compCode.'-'.str_pad((string) $identifier, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Print Single Certificate (A4 Landscape)
     */
    public function printSingle(Request $request)
    {
        $type = $request->query('type', 'juara');
        $registrationId = $request->query('registration_id');
        $competitionId = $request->query('competition_id');

        $registration = null;
        $competition = null;

        if ($registrationId) {
            $registration = Registration::with(['competition.category', 'members'])->findOrFail($registrationId);
            $competition = $registration->competition;
        } elseif ($competitionId) {
            $competition = Competition::findOrFail($competitionId);
        }

        // Get template
        $template = null;
        if ($competition) {
            $template = CertificateTemplate::where('type', $type)
                ->where('competition_id', $competition->id)
                ->where('is_active', true)
                ->first();
        }

        if (! $template) {
            $template = CertificateTemplate::where('type', $type)
                ->whereNull('competition_id')
                ->where('is_active', true)
                ->first();
        }

        if (! $template) {
            $template = new CertificateTemplate([
                'name' => 'Default '.ucfirst($type),
                'type' => $type,
                'layout_config' => CertificateTemplate::defaultLayoutConfig(),
                'number_format' => '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]',
            ]);
        }

        // Prepare single item data
        $certNumberSeq = $request->query('cert_seq', '001');
        $rankTitle = $request->query('rank', 'Juara 1');
        $recipientName = $request->query('name');
        $schoolName = $request->query('school');

        if ($registration) {
            $recipientName = $recipientName ?: ($registration->pure_name ?: ($registration->team_name ?: ($registration->members->first()?->full_name ?? ('Peserta #'.$registration->id))));
            $schoolName = $schoolName ?: ($registration->display_school ?: ($registration->institution_name ?: '-'));
        }

        $nowDate = Carbon::now();
        $dateSpelled = OfficialReportController::getDateSpelledOut($nowDate);

        // Format certificate number
        $monthRoman = match ((int) $nowDate->format('m')) {
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        };

        $certNumber = str_replace(
            ['[NO]', '[MONTH]', '[YEAR]'],
            [$certNumberSeq, $monthRoman, $nowDate->format('Y')],
            $template->number_format ?: '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]'
        );

        $certCode = $this->generateCertCode($type, $registration ? $registration->id : $certNumberSeq, $competition);
        $verifyUrl = QrSignatureService::certificateUrl($certCode);
        $qrSvg = QrSignatureService::generateSvg($verifyUrl, 85);

        $certificateItems = [
            [
                'cert_code' => $certCode,
                'cert_number' => $certNumber,
                'name' => $recipientName,
                'institution' => $schoolName,
                'predikat' => $type === 'juara' ? $rankTitle : ($type === 'peserta' ? 'Sebagai Peserta' : ($type === 'pembimbing' ? 'Sebagai Pembina / Pendamping' : 'Sebagai Dewan Juri / Wasit')),
                'competition_name' => $competition ? ($competition->name.($registration && $registration->sub_category ? ' ('.$registration->sub_category.')' : '')) : 'TALENTA MTsN 1 Blitar',
                'date_formatted' => 'Blitar, '.$dateSpelled['date_formatted'],
                'qr_svg' => $qrSvg,
                'verify_url' => $verifyUrl,
            ],
        ];

        return view('admin.certificates.print', compact('template', 'certificateItems'));
    }

    /**
     * Print Bulk Certificates (Multi-page A4 Landscape)
     */
    public function printBulk(Request $request)
    {
        $type = $request->query('type', 'juara');
        $competitionId = $request->query('competition_id');
        $competition = Competition::findOrFail($competitionId);
        $sectorFilter = $request->query('sector');

        // Find active template
        $template = CertificateTemplate::where('type', $type)
            ->where('competition_id', $competition->id)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            $template = CertificateTemplate::where('type', $type)
                ->whereNull('competition_id')
                ->where('is_active', true)
                ->first();
        }

        if (! $template) {
            $template = new CertificateTemplate([
                'name' => 'Default '.ucfirst($type),
                'type' => $type,
                'layout_config' => CertificateTemplate::defaultLayoutConfig(),
                'number_format' => '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]',
            ]);
        }

        $nowDate = Carbon::now();
        $dateSpelled = OfficialReportController::getDateSpelledOut($nowDate);
        $monthRoman = match ((int) $nowDate->format('m')) {
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        };

        $certificateItems = [];
        $counter = 1;

        if ($type === 'juara') {
            $sectorsDef = OfficialReportController::getCompetitionSectors($competition);
            if ($sectorFilter && isset($sectorsDef[$sectorFilter])) {
                $sectorsDef = [$sectorFilter => $sectorsDef[$sectorFilter]];
            }

            $regs = $competition->registrations()
                ->where('status', 'verified')
                ->with(['members', 'scores.details'])
                ->get();

            foreach ($sectorsDef as $secKey => $secDef) {
                $filteredRegs = $regs->filter(function ($r) use ($secDef, $competition) {
                    $isGanda = $r->isGanda();
                    $gender = $r->primary_gender;

                    if ($secDef['is_ganda'] && ! $isGanda) {
                        return false;
                    }
                    if (! $secDef['is_ganda'] && $isGanda && in_array($competition->code, ['BLT', 'TMJ'])) {
                        return false;
                    }
                    if ($secDef['gender'] !== 'all' && $gender !== $secDef['gender']) {
                        return false;
                    }

                    if ($secDef['kat'] === 'a') {
                        return $r->isKatA();
                    } elseif ($secDef['kat'] === 'b') {
                        return $r->isKatB();
                    } elseif ($secDef['kat'] === 'c') {
                        return $r->isKatC();
                    }

                    return true;
                });

                $ranked = $filteredRegs->map(function ($r) {
                    $lockedScores = $r->scores->where('is_locked', true);
                    $avgScore = $lockedScores->isNotEmpty() ? round($lockedScores->avg('total_score'), 2) : 0;
                    $participantName = $r->pure_name ?: ($r->team_name ?: ($r->members->first()?->full_name ?? ('Peserta #'.$r->id)));
                    $schoolName = $r->display_school ?: ($r->institution_name ?: '-');

                    return [
                        'registration' => $r,
                        'participant_number' => $r->participant_number ?: $r->registration_code,
                        'display_name' => $participantName,
                        'institution_name' => $schoolName,
                        'score' => $avgScore > 0 ? $avgScore : '',
                        'has_score' => ($lockedScores->isNotEmpty() && $avgScore > 0),
                    ];
                })
                    ->filter(fn ($item) => $item['has_score'])
                    ->sortByDesc(fn ($item) => (float) $item['score'])->values();

                $tierLabels = [
                    'Juara 1',
                    'Juara 2',
                    'Juara 3',
                    'Juara Harapan 1',
                    'Juara Harapan 2',
                    'Juara Harapan 3',
                ];

                foreach ($tierLabels as $idx => $tierLabel) {
                    if (isset($ranked[$idx])) {
                        $w = $ranked[$idx];
                        $reg = $w['registration'];
                        $certSeq = str_pad((string) $counter++, 3, '0', STR_PAD_LEFT);
                        $certNumber = str_replace(
                            ['[NO]', '[MONTH]', '[YEAR]'],
                            [$certSeq, $monthRoman, $nowDate->format('Y')],
                            $template->number_format ?: '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]'
                        );

                        $certCode = $this->generateCertCode('juara', $reg->id, $competition);
                        $verifyUrl = QrSignatureService::certificateUrl($certCode);
                        $qrSvg = QrSignatureService::generateSvg($verifyUrl, 85);

                        $compLabel = $competition->name;
                        if (! empty($secDef['title'])) {
                            $compLabel .= ' ('.$secDef['group'].')';
                        }

                        $certificateItems[] = [
                            'cert_code' => $certCode,
                            'cert_number' => $certNumber,
                            'name' => $w['display_name'],
                            'institution' => $w['institution_name'],
                            'predikat' => $tierLabel,
                            'competition_name' => $compLabel,
                            'date_formatted' => 'Blitar, '.$dateSpelled['date_formatted'],
                            'qr_svg' => $qrSvg,
                            'verify_url' => $verifyUrl,
                        ];
                    }
                }
            }
        } elseif ($type === 'peserta') {
            $regs = $competition->registrations()
                ->where('status', 'verified')
                ->with(['members'])
                ->orderBy('participant_number', 'asc')
                ->get();

            foreach ($regs as $r) {
                $certSeq = str_pad((string) $counter++, 3, '0', STR_PAD_LEFT);
                $certNumber = str_replace(
                    ['[NO]', '[MONTH]', '[YEAR]'],
                    [$certSeq, $monthRoman, $nowDate->format('Y')],
                    $template->number_format ?: '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]'
                );

                $certCode = $this->generateCertCode('peserta', $r->id, $competition);
                $verifyUrl = QrSignatureService::certificateUrl($certCode);
                $qrSvg = QrSignatureService::generateSvg($verifyUrl, 85);

                $participantName = $r->pure_name ?: ($r->team_name ?: ($r->members->first()?->full_name ?? ('Peserta #'.$r->id)));
                $schoolName = $r->display_school ?: ($r->institution_name ?: '-');

                $certificateItems[] = [
                    'cert_code' => $certCode,
                    'cert_number' => $certNumber,
                    'name' => $participantName,
                    'institution' => $schoolName,
                    'predikat' => 'Sebagai Peserta',
                    'competition_name' => $competition->name,
                    'date_formatted' => 'Blitar, '.$dateSpelled['date_formatted'],
                    'qr_svg' => $qrSvg,
                    'verify_url' => $verifyUrl,
                ];
            }
        } elseif ($type === 'pembimbing') {
            $regs = $competition->registrations()
                ->where('status', 'verified')
                ->whereNotNull('official_name')
                ->where('official_name', '!=', '')
                ->get();

            $uniqueOfficials = [];
            foreach ($regs as $r) {
                $key = Str::slug($r->official_name.'-'.($r->display_school ?: $r->institution_name));
                if (! isset($uniqueOfficials[$key])) {
                    $uniqueOfficials[$key] = [
                        'official_name' => $r->official_name,
                        'institution' => $r->display_school ?: ($r->institution_name ?: '-'),
                        'registration_id' => $r->id,
                    ];
                }
            }

            foreach ($uniqueOfficials as $off) {
                $certSeq = str_pad((string) $counter++, 3, '0', STR_PAD_LEFT);
                $certNumber = str_replace(
                    ['[NO]', '[MONTH]', '[YEAR]'],
                    [$certSeq, $monthRoman, $nowDate->format('Y')],
                    $template->number_format ?: '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]'
                );

                $certCode = $this->generateCertCode('pembimbing', $off['registration_id'], $competition);
                $verifyUrl = QrSignatureService::certificateUrl($certCode);
                $qrSvg = QrSignatureService::generateSvg($verifyUrl, 85);

                $certificateItems[] = [
                    'cert_code' => $certCode,
                    'cert_number' => $certNumber,
                    'name' => $off['official_name'],
                    'institution' => $off['institution'],
                    'predikat' => 'Sebagai Guru Pembimbing / Pendamping',
                    'competition_name' => $competition->name,
                    'date_formatted' => 'Blitar, '.$dateSpelled['date_formatted'],
                    'qr_svg' => $qrSvg,
                    'verify_url' => $verifyUrl,
                ];
            }
        } elseif ($type === 'juri') {
            $judges = $competition->judges;
            foreach ($judges as $j) {
                $certSeq = str_pad((string) $counter++, 3, '0', STR_PAD_LEFT);
                $certNumber = str_replace(
                    ['[NO]', '[MONTH]', '[YEAR]'],
                    [$certSeq, $monthRoman, $nowDate->format('Y')],
                    $template->number_format ?: '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]'
                );

                $certCode = $this->generateCertCode('juri', $j->id, $competition);
                $verifyUrl = QrSignatureService::certificateUrl($certCode);
                $qrSvg = QrSignatureService::generateSvg($verifyUrl, 85);

                $certificateItems[] = [
                    'cert_code' => $certCode,
                    'cert_number' => $certNumber,
                    'name' => $j->name,
                    'institution' => $j->institution ?? 'Dewan Juri / Wasit TALENTA',
                    'predikat' => 'Sebagai Dewan Juri / Wasit',
                    'competition_name' => $competition->name,
                    'date_formatted' => 'Blitar, '.$dateSpelled['date_formatted'],
                    'qr_svg' => $qrSvg,
                    'verify_url' => $verifyUrl,
                ];
            }
        }

        if (empty($certificateItems)) {
            return redirect()->back()->with('error', 'Tidak ada data peserta/pemenang yang dapat dicetak sertifikatnya untuk kategori ini.');
        }

        return view('admin.certificates.print', compact('template', 'certificateItems'));
    }

    /**
     * Download Certificate from Peserta Dashboard
     */
    public function pesertaDownload($id)
    {
        $user = Auth::user();
        $registration = Registration::with(['competition.category', 'members'])->findOrFail($id);

        if ($user->role === 'peserta' && $registration->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke sertifikat pendaftaran ini.');
        }

        // Check if certificate is released by committee
        $releaseSetting = AppSetting::where('key', 'certificate_release_'.$registration->competition_id)->first();
        if (! $releaseSetting || $releaseSetting->value !== '1') {
            return redirect()->route('peserta.registration.detail', $registration->id)
                ->with('error', 'Sertifikat untuk cabang lomba ini belum dirilis oleh panitia pelaksana.');
        }

        // Determine if participant is winner or participant
        $competition = $registration->competition;
        $type = 'peserta';
        $rankTitle = 'Sebagai Peserta';

        // Check winner status from scores
        $template = CertificateTemplate::where('type', $type)
            ->where('competition_id', $competition->id)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            $template = CertificateTemplate::where('type', $type)
                ->whereNull('competition_id')
                ->where('is_active', true)
                ->first();
        }

        if (! $template) {
            $template = new CertificateTemplate([
                'name' => 'Default '.ucfirst($type),
                'type' => $type,
                'layout_config' => CertificateTemplate::defaultLayoutConfig(),
                'number_format' => '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]',
            ]);
        }

        $nowDate = Carbon::now();
        $dateSpelled = OfficialReportController::getDateSpelledOut($nowDate);
        $monthRoman = match ((int) $nowDate->format('m')) {
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        };

        $certNumber = str_replace(
            ['[NO]', '[MONTH]', '[YEAR]'],
            [str_pad((string) $registration->id, 3, '0', STR_PAD_LEFT), $monthRoman, $nowDate->format('Y')],
            $template->number_format ?: '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]'
        );

        $certCode = $this->generateCertCode($type, $registration->id, $competition);
        $verifyUrl = QrSignatureService::certificateUrl($certCode);
        $qrSvg = QrSignatureService::generateSvg($verifyUrl, 85);

        $participantName = $registration->pure_name ?: ($registration->team_name ?: ($registration->members->first()?->full_name ?? ('Peserta #'.$registration->id)));
        $schoolName = $registration->display_school ?: ($registration->institution_name ?: '-');

        $certificateItems = [
            [
                'cert_code' => $certCode,
                'cert_number' => $certNumber,
                'name' => $participantName,
                'institution' => $schoolName,
                'predikat' => $rankTitle,
                'competition_name' => $competition->name,
                'date_formatted' => 'Blitar, '.$dateSpelled['date_formatted'],
                'qr_svg' => $qrSvg,
                'verify_url' => $verifyUrl,
            ],
        ];

        return view('admin.certificates.print', compact('template', 'certificateItems'));
    }

    /**
     * Public Verification Page when QR Code is scanned
     */
    public function verifyPublic($code)
    {
        // Parse certificate code: TLT-[TYPE]-[COMP_CODE]-[ID]
        $parts = explode('-', $code);
        $isValid = false;
        $certData = null;

        if (count($parts) >= 4 && $parts[0] === 'TLT') {
            $typePrefix = $parts[1]; // JRA, PST, PMB, JRI
            $compCode = $parts[2];
            $id = (int) $parts[3];

            $competition = Competition::where('code', $compCode)->first();
            $registration = Registration::with(['competition', 'members'])->find($id);

            if ($registration || $competition) {
                $isValid = true;
                $recipientName = '-';
                $schoolName = '-';
                $predikat = match ($typePrefix) {
                    'JRA' => 'Pemenang Kejuaraan',
                    'PST' => 'Sebagai Peserta',
                    'PMB' => 'Sebagai Guru Pembimbing / Pendamping',
                    'JRI' => 'Sebagai Dewan Juri / Wasit',
                    default => 'Penerima Sertifikat',
                };

                if ($registration) {
                    $recipientName = $registration->pure_name ?: ($registration->team_name ?: ($registration->members->first()?->full_name ?? ('Peserta #'.$registration->id)));
                    $schoolName = $registration->display_school ?: ($registration->institution_name ?: '-');
                    if ($typePrefix === 'PMB' && $registration->official_name) {
                        $recipientName = $registration->official_name;
                    }
                }

                $certData = [
                    'code' => $code,
                    'name' => $recipientName,
                    'institution' => $schoolName,
                    'competition' => $competition ? $competition->name : ($registration->competition->name ?? 'TALENTA MTsN 1 Blitar'),
                    'predikat' => $predikat,
                    'issued_at' => Carbon::now()->format('d F Y'),
                    'institution_issuer' => 'MTsN 1 Blitar - Kementerian Agama RI',
                ];
            }
        }

        return view('certificates.verify', compact('isValid', 'code', 'certData'));
    }
}
