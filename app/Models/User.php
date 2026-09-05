<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nisn',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'account_type',
        'institution_name',
        'position',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isPic(): bool
    {
        return $this->role === 'pic_lomba';
    }

    public function isJudge(): bool
    {
        return $this->role === 'juri';
    }

    public function isParticipant(): bool
    {
        return $this->role === 'peserta';
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'user_id');
    }

    public function managedCompetitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'pic_id');
    }

    public function judgedCompetitions()
    {
        return $this->belongsToMany(Competition::class, 'competition_judges', 'user_id', 'competition_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class, 'judge_id');
    }

    /**
     * Check if user is authorized to manage or umpire Badminton
     */
    public function managesBadminton(): bool
    {
        if ($this->role === 'superadmin') {
            return true;
        }

        if ($this->role === 'pic_lomba') {
            // Primary PIC for Bulu Tangkis
            if (Competition::where('code', 'BLT')->where('pic_id', $this->id)->exists()) {
                return true;
            }

            // Sector PICs in AppSetting
            $bltPics = array_filter([
                AppSetting::get('blt_pic_tunggal_pa'),
                AppSetting::get('blt_pic_tunggal_pi'),
                AppSetting::get('blt_pic_ganda_pa'),
                AppSetting::get('blt_pic_ganda_pi'),
            ]);

            return in_array($this->id, $bltPics) || in_array((string) $this->id, $bltPics);
        }

        if ($this->role === 'juri') {
            return $this->judgedCompetitions()->where('code', 'BLT')->exists();
        }

        return false;
    }
}
