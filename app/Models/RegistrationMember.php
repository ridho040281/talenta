<?php

namespace App\Models;

use App\Helpers\NameStandardizer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'full_name',
        'school_name',
        'nisn',
        'gender',
        'birth_place',
        'birth_date',
        'phone',
        'photo',
        'role_in_team',
    ];

    protected function casts(): array
    {
        return [];
    }

    protected $appends = [
        'formatted_birth_date',
    ];

    public function getBirthDateAttribute($value): ?string
    {
        if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function getFormattedBirthDateAttribute(): ?string
    {
        $raw = $this->attributes['birth_date'] ?? null;
        if (empty($raw) || $raw === '0000-00-00' || $raw === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return Carbon::parse($raw)->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            return (string) $raw;
        }
    }

    public function setFullNameAttribute($value): void
    {
        $this->attributes['full_name'] = NameStandardizer::format($value);
    }

    public function getFullNameAttribute($value): string
    {
        return NameStandardizer::format($value ?? '');
    }

    public function setSchoolNameAttribute($value): void
    {
        $this->attributes['school_name'] = ! empty($value) ? NameStandardizer::formatSchool($value) : null;
    }

    public function getSchoolNameAttribute($value): ?string
    {
        return ! empty($value) ? NameStandardizer::formatSchool($value) : null;
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
