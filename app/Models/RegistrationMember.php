<?php

namespace App\Models;

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
        return [
            'birth_date' => 'date:Y-m-d',
        ];
    }

    protected $appends = [
        'formatted_birth_date',
    ];

    public function getFormattedBirthDateAttribute(): ?string
    {
        if (! $this->birth_date) {
            return null;
        }

        try {
            return $this->birth_date instanceof \Carbon\Carbon
                ? $this->birth_date->translatedFormat('d F Y')
                : \Carbon\Carbon::parse($this->birth_date)->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            return (string) $this->birth_date;
        }
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
