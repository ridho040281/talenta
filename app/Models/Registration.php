<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'user_id',
        'invoice_id',
        'registration_code',
        'participant_number',
        'draw_number',
        'bracket_slot',
        'team_name',
        'sub_category',
        'chosen_song',
        'target_class',
        'match_type',
        'institution_name',
        'official_name',
        'official_phone',
        'official_gender',
        'official_photo',
        'status',
        'stage_status',
        'stage_duration_seconds',
        'is_collective',
        'payment_proof',
        'document_file',
        'verification_notes',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'draw_number' => 'integer',
            'stage_duration_seconds' => 'integer',
            'is_collective' => 'boolean',
        ];
    }

    protected $appends = [
        'display_school',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(RegistrationMember::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function drawAllocation(): HasMany
    {
        return $this->hasMany(DrawAllocation::class);
    }

    /**
     * Get display school / institution name for table & reports
     */
    public function getDisplaySchoolAttribute(): string
    {
        $members = $this->relationLoaded('members') ? $this->members : $this->members()->get();
        if ($members && $members->count() === 1 && ! empty($members->first()->school_name)) {
            return $members->first()->school_name;
        }

        if ($members && $members->count() > 1) {
            $uniqueSchools = $members->pluck('school_name')->filter()->unique();
            if ($uniqueSchools->count() > 1) {
                return $uniqueSchools->implode(' / ');
            } elseif ($uniqueSchools->count() === 1) {
                return $uniqueSchools->first();
            }
        }

        return $this->institution_name ?: '-';
    }

    public function getDisplayNameAttribute(): string
    {
        $school = $this->display_school;

        if ($this->team_name) {
            return $this->team_name.' ('.$school.')';
        }

        $firstMember = $this->relationLoaded('members') ? $this->members->first() : $this->members()->first();
        if ($firstMember) {
            return $firstMember->full_name.' ('.$school.')';
        }

        return 'Peserta #'.$this->id;
    }

    /**
     * Get participant or team name without the institution suffix
     */
    public function getPureNameAttribute(): string
    {
        if (! empty($this->team_name)) {
            return $this->team_name;
        }

        $firstMember = $this->members->first();
        if ($firstMember && ! empty($firstMember->full_name)) {
            return $firstMember->full_name;
        }

        return $this->user?->name ?? ('Peserta #'.$this->id);
    }

    public function getFeeAttribute(): float
    {
        if (! $this->competition) {
            return 0;
        }

        if ($this->competition->code === 'BLT') {
            $isGanda = $this->members->count() > 1 || (stripos($this->match_type ?? '', 'Ganda') !== false && stripos($this->match_type ?? '', 'Tunggal') === false) || (empty($this->match_type) && stripos($this->sub_category ?? '', 'Ganda') !== false && stripos($this->sub_category ?? '', 'Tunggal') === false);
            $isPutri = stripos($this->match_type ?? '', 'Putri') !== false || stripos($this->match_type ?? '', '(PI)') !== false || $this->primary_gender === 'P';

            if ($isGanda) {
                return (float) AppSetting::get($isPutri ? 'blt_fee_ganda_pi' : 'blt_fee_ganda_pa', 200000);
            }

            $feeA = (float) AppSetting::get($isPutri ? 'blt_fee_a_tunggal_pi' : 'blt_fee_a_tunggal_pa', 130000);
            $feeB = (float) AppSetting::get($isPutri ? 'blt_fee_b_tunggal_pi' : 'blt_fee_b_tunggal_pa', 150000);
            $feeC = (float) AppSetting::get($isPutri ? 'blt_fee_c_tunggal_pi' : 'blt_fee_c_tunggal_pa', 150000);

            if ($this->isKatA()) {
                return $feeA;
            } elseif ($this->isKatB()) {
                return $feeB;
            } elseif ($this->isKatC()) {
                return $feeC;
            }

            return $feeA;
        }

        if ($this->competition->code === 'TMJ') {
            $isPutri = stripos($this->match_type ?? '', 'Putri') !== false || stripos($this->match_type ?? '', '(PI)') !== false || $this->primary_gender === 'P';
            if ($this->isKatB()) {
                return (float) AppSetting::get($isPutri ? 'tmj_fee_b_tunggal_pi' : 'tmj_fee_b_tunggal_pa', $this->competition->registration_fee ?: 35000);
            }
            return (float) AppSetting::get($isPutri ? 'tmj_fee_a_tunggal_pi' : 'tmj_fee_a_tunggal_pa', $this->competition->registration_fee ?: 35000);
        }

        if ($this->competition->code === 'MTQ') {
            $isPutri = $this->primary_gender === 'P' || stripos($this->match_type ?? '', 'Putri') !== false || stripos($this->match_type ?? '', 'PI') !== false;
            return (float) AppSetting::get($isPutri ? 'mtq_fee_pi' : 'mtq_fee_pa', $this->competition->registration_fee);
        }

        if ($this->competition->code === 'POP') {
            $isPutri = $this->primary_gender === 'P' || stripos($this->match_type ?? '', 'Putri') !== false || stripos($this->match_type ?? '', 'PI') !== false;
            return (float) AppSetting::get($isPutri ? 'pop_fee_pi' : 'pop_fee_pa', $this->competition->registration_fee);
        }

        return (float) $this->competition->registration_fee;
    }

    public function getPrimaryGenderAttribute(): string
    {
        $genders = $this->members->pluck('gender')->filter();
        if ($genders->isEmpty()) {
            return 'U';
        }
        if ($genders->every(fn ($g) => $g === 'L')) {
            return 'L';
        }
        if ($genders->every(fn ($g) => $g === 'P')) {
            return 'P';
        }

        return 'M';
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->primary_gender) {
            'L' => 'Putra (PA)',
            'P' => 'Putri (PI)',
            'M' => 'Ganda Campuran',
            default => 'Umum',
        };
    }

    public function generateParticipantNumber(): string
    {
        if ($this->participant_number) {
            return $this->participant_number;
        }

        $comp = $this->competition ?? Competition::find($this->competition_id);
        $code = $comp ? $comp->code : 'REG';

        $count = self::where('competition_id', $this->competition_id)
            ->whereNotNull('participant_number')
            ->count() + 1;

        $participantNumber = $code.'-'.str_pad($count, 3, '0', STR_PAD_LEFT);
        while (self::where('competition_id', $this->competition_id)->where('participant_number', $participantNumber)->exists()) {
            $count++;
            $participantNumber = $code.'-'.str_pad($count, 3, '0', STR_PAD_LEFT);
        }

        $this->participant_number = $participantNumber;
        $this->saveQuietly();

        return $participantNumber;
    }

    public function getOfficialParticipantNumberAttribute(): string
    {
        if ($this->participant_number) {
            return $this->participant_number;
        }

        if ($this->status === 'verified') {
            return $this->generateParticipantNumber();
        }

        return 'Menunggu Verifikasi';
    }

    public function averageScore(): float
    {
        $lockedScores = $this->scores()->where('is_locked', true)->get();
        if ($lockedScores->isEmpty()) {
            return 0;
        }

        return round($lockedScores->avg('total_score'), 2);
    }

    /**
     * Get all unique formatted recipient phone numbers (Official, Account User & Participant Members)
     * Returns deduplicated array of clean phone numbers (e.g. ['62812...', '62857...'])
     */
    public function getRecipientPhonesAttribute(): array
    {
        $phones = [];

        // 1. Official phone
        if (! empty($this->official_phone)) {
            $phones[] = $this->official_phone;
        }

        // 2. Registrant / Account user phone
        if (! empty($this->user?->phone)) {
            $phones[] = $this->user->phone;
        }

        // 3. Member phones
        if ($this->relationLoaded('members') || $this->members()->exists()) {
            foreach ($this->members as $member) {
                if (! empty($member->phone)) {
                    $phones[] = $member->phone;
                }
            }
        }

        // Clean & format to standard Indonesian 628xxx
        $cleanPhones = [];
        foreach ($phones as $p) {
            $clean = preg_replace('/[^0-9]/', '', (string) $p);
            if (empty($clean)) {
                continue;
            }
            if (str_starts_with($clean, '0')) {
                $clean = '62'.substr($clean, 1);
            } elseif (str_starts_with($clean, '8')) {
                $clean = '628'.substr($clean, 1);
            }
            $cleanPhones[] = $clean;
        }

        return array_values(array_unique($cleanPhones));
    }

    /**
     * Get the designated PIC User for this registration based on competition & sector settings.
     */
    public function getPicUserAttribute(): ?User
    {
        $comp = $this->competition;
        if (! $comp) {
            return $this->verifier;
        }

        $isGanda = $this->members->count() > 1 || (stripos($this->match_type ?? '', 'ganda') !== false && stripos($this->match_type ?? '', 'tunggal') === false) || (empty($this->match_type) && stripos($this->sub_category ?? '', 'ganda') !== false && stripos($this->sub_category ?? '', 'tunggal') === false);
        $isPa = ($this->primary_gender === 'L');

        if ($comp->code === 'BLT') {
            if ($isGanda) {
                return $isPa ? ($comp->pic_ganda_pa ?: $comp->pic) : ($comp->pic_ganda_pi ?: $comp->pic);
            } else {
                return $isPa ? ($comp->pic_tunggal_pa ?: $comp->pic) : ($comp->pic_tunggal_pi ?: $comp->pic);
            }
        }

        if (in_array($comp->code, ['TMJ', 'MTQ', 'POP'])) {
            return $isPa ? ($comp->pic_pa ?: $comp->pic) : ($comp->pic_pi ?: $comp->pic);
        }

        return $comp->pic ?: $this->verifier;
    }

    /**
     * Get the designated PIC Name for this registration.
     */
    public function getPicNameAttribute(): string
    {
        return $this->pic_user?->name ?: ($this->competition?->pic?->name ?: ($this->verifier?->name ?: 'PANITIA PELAKSANA'));
    }

    public function isGanda(): bool
    {
        return $this->members->count() > 1
            || (stripos($this->match_type ?? '', 'ganda') !== false && stripos($this->match_type ?? '', 'tunggal') === false)
            || (empty($this->match_type) && stripos($this->sub_category ?? '', 'ganda') !== false && stripos($this->sub_category ?? '', 'tunggal') === false);
    }

    public function isKatA(): bool
    {
        $targetStr = strtolower(($this->target_class ?? '') . ' ' . ($this->sub_category ?? '') . ' ' . ($this->team_name ?? '') . ' ' . ($this->match_type ?? ''));
        if ($this->competition?->code === 'TMJ') {
            return stripos($targetStr, 'kategori a') !== false || stripos($targetStr, 'kat a') !== false || stripos($targetStr, '1 - 3') !== false || stripos($targetStr, '1-3') !== false || stripos($targetStr, 'kelas 1') !== false || stripos($targetStr, 'kelas 2') !== false || stripos($targetStr, 'kelas 3') !== false || stripos($targetStr, '-a-') !== false || stripos($targetStr, 'kat_a') !== false;
        }
        return stripos($targetStr, 'kategori a') !== false || stripos($targetStr, 'kat a') !== false || stripos($targetStr, 'kelas 1') !== false || stripos($targetStr, 'kelas 2') !== false || stripos($targetStr, '-a-') !== false || stripos($targetStr, 'kat_a') !== false;
    }

    public function isKatB(): bool
    {
        $targetStr = strtolower(($this->target_class ?? '') . ' ' . ($this->sub_category ?? '') . ' ' . ($this->team_name ?? '') . ' ' . ($this->match_type ?? ''));
        if ($this->competition?->code === 'TMJ') {
            return stripos($targetStr, 'kategori b') !== false || stripos($targetStr, 'kat b') !== false || stripos($targetStr, '4 - 6') !== false || stripos($targetStr, '4-6') !== false || stripos($targetStr, 'kelas 4') !== false || stripos($targetStr, 'kelas 5') !== false || stripos($targetStr, 'kelas 6') !== false || stripos($targetStr, '-b-') !== false || stripos($targetStr, 'kat_b') !== false;
        }
        return stripos($targetStr, 'kategori b') !== false || stripos($targetStr, 'kat b') !== false || stripos($targetStr, 'kelas 3') !== false || stripos($targetStr, 'kelas 4') !== false || stripos($targetStr, '-b-') !== false || stripos($targetStr, 'kat_b') !== false;
    }

    public function isKatC(): bool
    {
        $targetStr = strtolower(($this->target_class ?? '') . ' ' . ($this->sub_category ?? '') . ' ' . ($this->team_name ?? '') . ' ' . ($this->match_type ?? ''));
        return stripos($targetStr, 'kategori c') !== false || stripos($targetStr, 'kat c') !== false || stripos($targetStr, 'kelas 5') !== false || stripos($targetStr, 'kelas 6') !== false || stripos($targetStr, '-c-') !== false || stripos($targetStr, 'kat_c') !== false;
    }
}
