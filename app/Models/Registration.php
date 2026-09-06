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
        'target_class',
        'match_type',
        'institution_name',
        'official_name',
        'official_phone',
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

    public function getDisplayNameAttribute(): string
    {
        if ($this->team_name) {
            return $this->team_name.' ('.$this->institution_name.')';
        }

        $firstMember = $this->members->first();
        if ($firstMember) {
            return $firstMember->full_name.' ('.$this->institution_name.')';
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
            $isGanda = stripos($this->match_type ?? '', 'Ganda') !== false || stripos($this->sub_category ?? '', 'Ganda') !== false || stripos($this->team_name ?? '', 'Ganda') !== false;
            $isPutri = stripos($this->match_type ?? '', 'Putri') !== false || stripos($this->match_type ?? '', '(PI)') !== false || $this->primary_gender === 'P';

            if ($isGanda) {
                return (float) AppSetting::get($isPutri ? 'blt_fee_ganda_pi' : 'blt_fee_ganda_pa', 200000);
            }

            $feeA = (float) AppSetting::get($isPutri ? 'blt_fee_a_tunggal_pi' : 'blt_fee_a_tunggal_pa', 130000);
            $feeB = (float) AppSetting::get($isPutri ? 'blt_fee_b_tunggal_pi' : 'blt_fee_b_tunggal_pa', 150000);
            $feeC = (float) AppSetting::get($isPutri ? 'blt_fee_c_tunggal_pi' : 'blt_fee_c_tunggal_pa', 150000);

            $target = ($this->target_class ?? '').' '.($this->sub_category ?? '');
            if (stripos($target, 'Kategori A') !== false || stripos($target, 'Kat A') !== false || stripos($target, '-A-') !== false) {
                return $feeA;
            } elseif (stripos($target, 'Kategori B') !== false || stripos($target, 'Kat B') !== false || stripos($target, '-B-') !== false) {
                return $feeB;
            } elseif (stripos($target, 'Kategori C') !== false || stripos($target, 'Kat C') !== false || stripos($target, '-C-') !== false) {
                return $feeC;
            }

            return $feeA;
        }

        if ($this->competition->code === 'TMJ') {
            $isPutri = stripos($this->match_type ?? '', 'Putri') !== false || stripos($this->match_type ?? '', '(PI)') !== false || $this->primary_gender === 'P';
            $target = ($this->target_class ?? '').' '.($this->sub_category ?? '');
            if (stripos($target, 'Kategori B') !== false || stripos($target, 'Kat B') !== false || stripos($target, '4 - 6') !== false) {
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
}
