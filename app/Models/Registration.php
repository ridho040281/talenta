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
            return $this->team_name . ' (' . $this->institution_name . ')';
        }

        $firstMember = $this->members->first();
        if ($firstMember) {
            return $firstMember->full_name . ' (' . $this->institution_name . ')';
        }

        return 'Peserta #' . $this->id;
    }

    public function getFeeAttribute(): float
    {
        if (!$this->competition) {
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

            $target = ($this->target_class ?? '') . ' ' . ($this->sub_category ?? '');
            if (stripos($target, 'Kategori A') !== false || stripos($target, 'Kat A') !== false || stripos($target, '-A-') !== false) {
                return $feeA;
            } elseif (stripos($target, 'Kategori B') !== false || stripos($target, 'Kat B') !== false || stripos($target, '-B-') !== false) {
                return $feeB;
            } elseif (stripos($target, 'Kategori C') !== false || stripos($target, 'Kat C') !== false || stripos($target, '-C-') !== false) {
                return $feeC;
            }
            return $feeA;
        }

        return (float) $this->competition->registration_fee;
    }

    public function getPrimaryGenderAttribute(): string
    {
        $genders = $this->members->pluck('gender')->filter();
        if ($genders->isEmpty()) {
            return 'U';
        }
        if ($genders->every(fn($g) => $g === 'L')) {
            return 'L';
        }
        if ($genders->every(fn($g) => $g === 'P')) {
            return 'P';
        }
        return 'M';
    }

    public function getGenderLabelAttribute(): string
    {
        return match($this->primary_gender) {
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

        $participantNumber = $code . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
        while (self::where('competition_id', $this->competition_id)->where('participant_number', $participantNumber)->exists()) {
            $count++;
            $participantNumber = $code . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
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
}
