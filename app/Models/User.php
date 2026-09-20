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

    public function isPanitia(): bool
    {
        return $this->role === 'panitia';
    }

    public function isPic(): bool
    {
        return $this->role === 'pic_lomba';
    }

    public function isJudge(): bool
    {
        return $this->role === 'juri';
    }

    public function getJudgeRoleDisplayAttribute(): string
    {
        if ($this->role !== 'juri') {
            return match ($this->role) {
                'superadmin' => '👑 Super Admin',
                'panitia' => '🎗️ Panitia Pelaksana',
                'pic_lomba' => '🛡️ PIC Lomba',
                default => '🎓 Peserta'
            };
        }

        $judged = $this->relationLoaded('judgedCompetitions')
            ? $this->judgedCompetitions
            : $this->judgedCompetitions()->with('category')->get();

        if ($judged->isEmpty()) {
            return '⚖️ Dewan Juri / Wasit';
        }

        $allSports = $judged->every(fn ($c) => $c->isSports());
        $allNonSports = $judged->every(fn ($c) => ! $c->isSports());

        if ($allSports) {
            return '🏁 Wasit';
        } elseif ($allNonSports) {
            return '⚖️ Dewan Juri';
        }

        return '⚖️ Juri & Wasit';
    }

    public function getJudgeTitleAttribute(): string
    {
        if ($this->role !== 'juri') {
            return match ($this->role) {
                'superadmin' => 'Super Administrator',
                'panitia' => 'Panitia Pelaksana',
                'pic_lomba' => 'PIC Koordinator',
                default => 'Pendaftar Resmi'
            };
        }

        $judged = $this->relationLoaded('judgedCompetitions')
            ? $this->judgedCompetitions
            : $this->judgedCompetitions()->with('category')->get();

        if ($judged->isEmpty()) {
            return 'Dewan Juri / Wasit';
        }

        $allSports = $judged->every(fn ($c) => $c->isSports());
        $allNonSports = $judged->every(fn ($c) => ! $c->isSports());

        if ($allSports) {
            return 'Wasit Pertandingan';
        } elseif ($allNonSports) {
            return 'Dewan Juri';
        }

        return 'Dewan Juri & Wasit';
    }

    public function isParticipant(): bool
    {
        return $this->role === 'peserta';
    }

    public function isTester(): bool
    {
        return $this->role === 'superadmin'
            || str_ends_with(strtolower($this->email ?? ''), '@talenta.test')
            || str_starts_with(strtolower($this->nisn ?? ''), 'tester_')
            || str_starts_with(strtolower($this->name ?? ''), 'tester');
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
        return $this->belongsToMany(Competition::class, 'competition_judges', 'user_id', 'competition_id')
            ->withPivot('role_title')
            ->withTimestamps();
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
        if (in_array($this->role, ['superadmin', 'panitia'])) {
            return true;
        }

        if ($this->role === 'pic_lomba') {
            // Primary PIC for Bulu Tangkis
            if (Competition::where(function ($q) {
                $q->where('code', 'BLT')
                    ->orWhere('name', 'like', '%Bulu Tangkis%')
                    ->orWhere('name', 'like', '%Badminton%');
            })->where('pic_id', $this->id)->exists()) {
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
            return $this->judgedCompetitions()->where(function ($q) {
                $q->where('code', 'BLT')
                    ->orWhere('name', 'like', '%Bulu Tangkis%')
                    ->orWhere('name', 'like', '%Badminton%');
            })->exists();
        }

        return false;
    }

    /**
     * Check if user manages Tenis Meja
     */
    public function managesTenisMeja(): bool
    {
        if (in_array($this->role, ['superadmin', 'panitia'])) {
            return true;
        }

        if ($this->role === 'pic_lomba') {
            return Competition::where(function ($q) {
                $q->where('code', 'TMJ')
                    ->orWhere('name', 'like', '%Tenis Meja%');
            })->where('pic_id', $this->id)
                ->exists();
        }

        if ($this->role === 'juri') {
            return $this->judgedCompetitions()
                ->where(function ($q) {
                    $q->where('code', 'TMJ')
                        ->orWhere('name', 'like', '%Tenis Meja%');
                })
                ->exists();
        }

        return false;
    }

    /**
     * Check if user is authorized to manage or umpire tournament sports (Bulu Tangkis & Tenis Meja)
     */
    public function managesTournamentBracket(): bool
    {
        if (in_array($this->role, ['superadmin', 'panitia'])) {
            return true;
        }

        if ($this->role === 'pic_lomba') {
            if ($this->managesBadminton() || $this->managesTenisMeja()) {
                return true;
            }

            return $this->managedCompetitions()
                ->where(function ($q) {
                    $q->whereIn('code', ['BLT', 'TMJ'])
                        ->orWhere('name', 'like', '%Bulu Tangkis%')
                        ->orWhere('name', 'like', '%Badminton%')
                        ->orWhere('name', 'like', '%Tenis Meja%');
                })
                ->exists();
        }

        if ($this->role === 'juri') {
            return $this->judgedCompetitions()
                ->where(function ($q) {
                    $q->whereIn('code', ['BLT', 'TMJ'])
                        ->orWhere('name', 'like', '%Bulu Tangkis%')
                        ->orWhere('name', 'like', '%Badminton%')
                        ->orWhere('name', 'like', '%Tenis Meja%');
                })
                ->exists();
        }

        return false;
    }
}
