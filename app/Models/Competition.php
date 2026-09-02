<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'pic_id',
        'name',
        'slug',
        'code',
        'type',
        'min_members',
        'max_members',
        'quota',
        'registration_fee',
        'rules',
        'guidelines_file',
        'venue',
        'schedule_date',
        'schedule_time',
        'status',
        'has_draw',
        'draw_status',
        'show_criteria',
        'is_live_score',
        'order',
    ];

    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('competitions.order', 'asc')->orderBy('competitions.id', 'asc');
        });
    }

    protected $appends = [
        'tier_fees',
        'tier_quotas',
        'status_a_tunggal_pa',
        'status_b_tunggal_pa',
        'status_c_tunggal_pa',
        'status_a_tunggal_pi',
        'status_b_tunggal_pi',
        'status_c_tunggal_pi',
        'status_ganda_pa',
        'status_ganda_pi',
        'status_tunggal_pa',
        'status_tunggal_pi',
        'status_pa',
        'status_pi',
        'guidelines_embed_url',
        'guidelines_download_url',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'has_draw' => 'boolean',
            'show_criteria' => 'boolean',
            'is_live_score' => 'boolean',
            'registration_fee' => 'decimal:2',
            'order' => 'integer',
        ];
    }

    public function getTierFeesAttribute(): array
    {
        if ($this->code === 'BLT') {
            $tunggalA_pa = (float) AppSetting::get('blt_fee_a_tunggal_pa', AppSetting::get('blt_fee_a_tunggal', 130000));
            $tunggalB_pa = (float) AppSetting::get('blt_fee_b_tunggal_pa', AppSetting::get('blt_fee_b_tunggal', 150000));
            $tunggalC_pa = (float) AppSetting::get('blt_fee_c_tunggal_pa', AppSetting::get('blt_fee_c_tunggal', 150000));
            $tunggalA_pi = (float) AppSetting::get('blt_fee_a_tunggal_pi', AppSetting::get('blt_fee_a_tunggal', 130000));
            $tunggalB_pi = (float) AppSetting::get('blt_fee_b_tunggal_pi', AppSetting::get('blt_fee_b_tunggal', 150000));
            $tunggalC_pi = (float) AppSetting::get('blt_fee_c_tunggal_pi', AppSetting::get('blt_fee_c_tunggal', 150000));
            $gandaFee_pa = (float) AppSetting::get('blt_fee_ganda_pa', AppSetting::get('blt_fee_ganda', 200000));
            $gandaFee_pi = (float) AppSetting::get('blt_fee_ganda_pi', AppSetting::get('blt_fee_ganda', 200000));

            return [
                'A_tunggal_pa' => $tunggalA_pa,
                'B_tunggal_pa' => $tunggalB_pa,
                'C_tunggal_pa' => $tunggalC_pa,
                'A_tunggal_pi' => $tunggalA_pi,
                'B_tunggal_pi' => $tunggalB_pi,
                'C_tunggal_pi' => $tunggalC_pi,
                'ganda_pa'     => $gandaFee_pa,
                'ganda_pi'     => $gandaFee_pi,
                // Fallbacks
                'A_tunggal'    => $tunggalA_pa,
                'B_tunggal'    => $tunggalB_pa,
                'C_tunggal'    => $tunggalC_pa,
                'ganda'        => $gandaFee_pa,
                'A'            => $tunggalA_pa,
                'B'            => $tunggalB_pa,
                'C'            => $tunggalC_pa,
            ];
        }

        if ($this->code === 'MTQ') {
            return [
                'pa' => (float) AppSetting::get('mtq_fee_pa', $this->registration_fee),
                'pi' => (float) AppSetting::get('mtq_fee_pi', $this->registration_fee),
            ];
        }

        if ($this->code === 'POP') {
            return [
                'pa' => (float) AppSetting::get('pop_fee_pa', $this->registration_fee),
                'pi' => (float) AppSetting::get('pop_fee_pi', $this->registration_fee),
            ];
        }

        if ($this->code === 'TMJ') {
            $a_pa = (float) AppSetting::get('tmj_fee_a_tunggal_pa', $this->registration_fee ?: 35000);
            $b_pa = (float) AppSetting::get('tmj_fee_b_tunggal_pa', $this->registration_fee ?: 35000);
            $a_pi = (float) AppSetting::get('tmj_fee_a_tunggal_pi', $this->registration_fee ?: 35000);
            $b_pi = (float) AppSetting::get('tmj_fee_b_tunggal_pi', $this->registration_fee ?: 35000);

            return [
                'A_tunggal_pa' => $a_pa,
                'B_tunggal_pa' => $b_pa,
                'A_tunggal_pi' => $a_pi,
                'B_tunggal_pi' => $b_pi,
                'A_tunggal'    => $a_pa,
                'B_tunggal'    => $b_pa,
                'A'            => $a_pa,
                'B'            => $b_pa,
                'pa'           => $a_pa,
                'pi'           => $a_pi,
            ];
        }

        return [];
    }

    public function getTierQuotasAttribute(): array
    {
        if ($this->code === 'BLT') {
            return [
                'A_tunggal_pa' => (int) AppSetting::get('blt_quota_a_tunggal_pa', (int) AppSetting::get('blt_quota_a_tunggal', 16)),
                'B_tunggal_pa' => (int) AppSetting::get('blt_quota_b_tunggal_pa', (int) AppSetting::get('blt_quota_b_tunggal', 16)),
                'C_tunggal_pa' => (int) AppSetting::get('blt_quota_c_tunggal_pa', (int) AppSetting::get('blt_quota_c_tunggal', 16)),
                'A_tunggal_pi' => (int) AppSetting::get('blt_quota_a_tunggal_pi', (int) AppSetting::get('blt_quota_a_tunggal', 16)),
                'B_tunggal_pi' => (int) AppSetting::get('blt_quota_b_tunggal_pi', (int) AppSetting::get('blt_quota_b_tunggal', 16)),
                'C_tunggal_pi' => (int) AppSetting::get('blt_quota_c_tunggal_pi', (int) AppSetting::get('blt_quota_c_tunggal', 16)),
                'ganda_pa'     => (int) AppSetting::get('blt_quota_ganda_pa', (int) AppSetting::get('blt_quota_ganda', 10)),
                'ganda_pi'     => (int) AppSetting::get('blt_quota_ganda_pi', (int) AppSetting::get('blt_quota_ganda', 10)),
                // Fallbacks
                'A_tunggal'    => (int) AppSetting::get('blt_quota_a_tunggal_pa', 16),
                'B_tunggal'    => (int) AppSetting::get('blt_quota_b_tunggal_pa', 16),
                'C_tunggal'    => (int) AppSetting::get('blt_quota_c_tunggal_pa', 16),
                'ganda'        => (int) AppSetting::get('blt_quota_ganda_pa', 10),
            ];
        }

        if ($this->code === 'MTQ') {
            return [
                'pa' => (int) AppSetting::get('mtq_quota_pa', (int) ceil($this->quota / 2)),
                'pi' => (int) AppSetting::get('mtq_quota_pi', (int) floor($this->quota / 2)),
            ];
        }

        if ($this->code === 'POP') {
            return [
                'pa' => (int) AppSetting::get('pop_quota_pa', (int) ceil($this->quota / 2)),
                'pi' => (int) AppSetting::get('pop_quota_pi', (int) floor($this->quota / 2)),
            ];
        }

        if ($this->code === 'TMJ') {
            $defaultQuota = (int) max(1, floor($this->quota / 4));
            return [
                'A_tunggal_pa' => (int) AppSetting::get('tmj_quota_a_tunggal_pa', $defaultQuota),
                'B_tunggal_pa' => (int) AppSetting::get('tmj_quota_b_tunggal_pa', $defaultQuota),
                'A_tunggal_pi' => (int) AppSetting::get('tmj_quota_a_tunggal_pi', $defaultQuota),
                'B_tunggal_pi' => (int) AppSetting::get('tmj_quota_b_tunggal_pi', $defaultQuota),
                'A_tunggal'    => (int) AppSetting::get('tmj_quota_a_tunggal_pa', $defaultQuota),
                'B_tunggal'    => (int) AppSetting::get('tmj_quota_b_tunggal_pa', $defaultQuota),
            ];
        }

        return [];
    }

    public function getTierPicsAttribute(): array
    {
        if ($this->code === 'BLT') {
            return [
                'tunggal_pa' => (int) AppSetting::get('blt_pic_tunggal_pa', $this->pic_id),
                'tunggal_pi' => (int) AppSetting::get('blt_pic_tunggal_pi', $this->pic_id),
                'ganda_pa'   => (int) AppSetting::get('blt_pic_ganda_pa', $this->pic_id),
                'ganda_pi'   => (int) AppSetting::get('blt_pic_ganda_pi', $this->pic_id),
            ];
        }

        if ($this->code === 'MTQ') {
            return [
                'pa' => (int) AppSetting::get('mtq_pic_pa', $this->pic_id),
                'pi' => (int) AppSetting::get('mtq_pic_pi', $this->pic_id),
            ];
        }

        if ($this->code === 'POP') {
            return [
                'pa' => (int) AppSetting::get('pop_pic_pa', $this->pic_id),
                'pi' => (int) AppSetting::get('pop_pic_pi', $this->pic_id),
            ];
        }

        if ($this->code === 'TMJ') {
            return [
                'tunggal_pa' => (int) AppSetting::get('tmj_pic_tunggal_pa', $this->pic_id),
                'tunggal_pi' => (int) AppSetting::get('tmj_pic_tunggal_pi', $this->pic_id),
                'pa'         => (int) AppSetting::get('tmj_pic_tunggal_pa', $this->pic_id),
                'pi'         => (int) AppSetting::get('tmj_pic_tunggal_pi', $this->pic_id),
            ];
        }

        return [];
    }

    public function getPicPaAttribute()
    {
        if ($this->code === 'MTQ') {
            $id = AppSetting::get('mtq_pic_pa', $this->pic_id);
            return $id ? User::find($id) : $this->pic;
        }
        if ($this->code === 'POP') {
            $id = AppSetting::get('pop_pic_pa', $this->pic_id);
            return $id ? User::find($id) : $this->pic;
        }
        if ($this->code === 'TMJ') {
            $id = AppSetting::get('tmj_pic_tunggal_pa', $this->pic_id);
            return $id ? User::find($id) : $this->pic;
        }
        if ($this->code === 'BLT') {
            return $this->pic_tunggal_pa;
        }
        return $this->pic;
    }

    public function getPicPiAttribute()
    {
        if ($this->code === 'MTQ') {
            $id = AppSetting::get('mtq_pic_pi', $this->pic_id);
            return $id ? User::find($id) : $this->pic;
        }
        if ($this->code === 'POP') {
            $id = AppSetting::get('pop_pic_pi', $this->pic_id);
            return $id ? User::find($id) : $this->pic;
        }
        if ($this->code === 'TMJ') {
            $id = AppSetting::get('tmj_pic_tunggal_pi', $this->pic_id);
            return $id ? User::find($id) : $this->pic;
        }
        if ($this->code === 'BLT') {
            return $this->pic_tunggal_pi;
        }
        return $this->pic;
    }

    public function getStatusPaAttribute(): string
    {
        if ($this->code === 'MTQ') {
            return AppSetting::get('mtq_status_pa', $this->status ?? 'buka');
        }
        if ($this->code === 'POP') {
            return AppSetting::get('pop_status_pa', $this->status ?? 'buka');
        }
        if ($this->code === 'TMJ') {
            return AppSetting::get('tmj_status_tunggal_pa', $this->status ?? 'buka');
        }
        if ($this->code === 'BLT') {
            return $this->status_tunggal_pa;
        }
        return $this->status ?? 'buka';
    }

    public function getStatusPiAttribute(): string
    {
        if ($this->code === 'MTQ') {
            return AppSetting::get('mtq_status_pi', $this->status ?? 'buka');
        }
        if ($this->code === 'POP') {
            return AppSetting::get('pop_status_pi', $this->status ?? 'buka');
        }
        if ($this->code === 'TMJ') {
            return AppSetting::get('tmj_status_tunggal_pi', $this->status ?? 'buka');
        }
        if ($this->code === 'BLT') {
            return $this->status_tunggal_pi;
        }
        return $this->status ?? 'buka';
    }

    public function getPicTunggalPaAttribute()
    {
        $id = AppSetting::get('blt_pic_tunggal_pa', $this->pic_id);
        return $id ? User::find($id) : $this->pic;
    }

    public function getPicTunggalPiAttribute()
    {
        $id = AppSetting::get('blt_pic_tunggal_pi', $this->pic_id);
        return $id ? User::find($id) : $this->pic;
    }

    public function getPicGandaPaAttribute()
    {
        $id = AppSetting::get('blt_pic_ganda_pa', $this->pic_id);
        return $id ? User::find($id) : $this->pic;
    }

    public function getPicGandaPiAttribute()
    {
        $id = AppSetting::get('blt_pic_ganda_pi', $this->pic_id);
        return $id ? User::find($id) : $this->pic;
    }

    public function getStatusATunggalPaAttribute(): string
    {
        $prefix = strtolower($this->code);
        return AppSetting::get($prefix . '_status_a_tunggal_pa', AppSetting::get($prefix . '_status_tunggal_pa', $this->status ?? 'buka'));
    }

    public function getStatusBTunggalPaAttribute(): string
    {
        $prefix = strtolower($this->code);
        return AppSetting::get($prefix . '_status_b_tunggal_pa', AppSetting::get($prefix . '_status_tunggal_pa', $this->status ?? 'buka'));
    }

    public function getStatusCTunggalPaAttribute(): string
    {
        $prefix = strtolower($this->code);
        return AppSetting::get($prefix . '_status_c_tunggal_pa', AppSetting::get($prefix . '_status_tunggal_pa', $this->status ?? 'buka'));
    }

    public function getStatusATunggalPiAttribute(): string
    {
        $prefix = strtolower($this->code);
        return AppSetting::get($prefix . '_status_a_tunggal_pi', AppSetting::get($prefix . '_status_tunggal_pi', $this->status ?? 'buka'));
    }

    public function getStatusBTunggalPiAttribute(): string
    {
        $prefix = strtolower($this->code);
        return AppSetting::get($prefix . '_status_b_tunggal_pi', AppSetting::get($prefix . '_status_tunggal_pi', $this->status ?? 'buka'));
    }

    public function getStatusCTunggalPiAttribute(): string
    {
        $prefix = strtolower($this->code);
        return AppSetting::get($prefix . '_status_c_tunggal_pi', AppSetting::get($prefix . '_status_tunggal_pi', $this->status ?? 'buka'));
    }

    public function getStatusTunggalPaAttribute(): string
    {
        $prefix = strtolower($this->code);
        return AppSetting::get($prefix . '_status_tunggal_pa', $this->status ?? 'buka');
    }

    public function getStatusTunggalPiAttribute(): string
    {
        $prefix = strtolower($this->code);
        return AppSetting::get($prefix . '_status_tunggal_pi', $this->status ?? 'buka');
    }

    public function getStatusGandaPaAttribute(): string
    {
        return AppSetting::get('blt_status_ganda_pa', $this->status ?? 'buka');
    }

    public function getStatusGandaPiAttribute(): string
    {
        return AppSetting::get('blt_status_ganda_pi', $this->status ?? 'buka');
    }

    public function getGuidelinesEmbedUrlAttribute(): ?string
    {
        $val = trim($this->guidelines_file ?? '');
        if (!$val) return null;

        // If full iframe snippet, extract src
        if (preg_match('/src=["\']([^"\']+)["\']/', $val, $matches)) {
            $val = $matches[1];
        }

        // Google Drive link: /file/d/{ID}/view... -> /file/d/{ID}/preview
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/preview";
        }

        // Google Drive link: open?id={ID}
        if (preg_match('/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/preview";
        }

        // Google Docs link: /document/d/{ID}/edit... -> /preview
        if (preg_match('/docs\.google\.com\/document\/d\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://docs.google.com/document/d/{$matches[1]}/preview";
        }

        // Local uploaded file path
        if (!str_starts_with($val, 'http://') && !str_starts_with($val, 'https://')) {
            return asset('storage/' . ltrim($val, '/'));
        }

        return $val;
    }

    public function getGuidelinesDownloadUrlAttribute(): ?string
    {
        $val = trim($this->guidelines_file ?? '');
        if (!$val) return null;

        // If full iframe snippet, extract src
        if (preg_match('/src=["\']([^"\']+)["\']/', $val, $matches)) {
            $val = $matches[1];
        }

        // Google Drive link: /file/d/{ID}/view
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/view?usp=sharing";
        }

        // Google Drive link: open?id={ID}
        if (preg_match('/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/view?usp=sharing";
        }

        // Local uploaded file path
        if (!str_starts_with($val, 'http://') && !str_starts_with($val, 'https://')) {
            return asset('storage/' . ltrim($val, '/'));
        }

        return $val;
    }

    public function getTierFee(string $tier): float
    {
        $tier = strtoupper(trim($tier));
        $tiers = $this->tier_fees;
        return $tiers[$tier] ?? (float) $this->registration_fee;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(CompetitionCriterion::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function verifiedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class)->where('status', 'verified');
    }

    public function judges(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'competition_judges', 'competition_id', 'user_id')
                    ->withPivot('role_title')
                    ->withTimestamps();
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function drawAllocations(): HasMany
    {
        return $this->hasMany(DrawAllocation::class);
    }

    public function isIndividual(): bool
    {
        return $this->type === 'individu';
    }

    public function isCollective(): bool
    {
        return in_array($this->type, ['kolektif', 'tim', 'kelompok', 'regu']);
    }

    public function isUnlimitedQuota(): bool
    {
        return empty($this->quota) || $this->quota <= 0;
    }

    public function getFormattedQuotaAttribute(): string
    {
        if ($this->isUnlimitedQuota()) {
            return 'Tak Terbatas';
        }
        return (string) $this->quota;
    }
}
