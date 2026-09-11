<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'show_rules',
        'guidelines_file',
        'show_guidelines',
        'whatsapp_group_url',
        'venue',
        'schedule_date',
        'schedule_time',
        'registration_start_at',
        'registration_end_at',
        'status',
        'has_draw',
        'draw_status',
        'show_criteria',
        'is_live_score',
        'has_stage_timer',
        'stage_duration_minutes',
        'stage_warning_minutes',
        'stage_overtime_minutes',
        'stage_bell_sound',
        'stage_state',
        'order',
    ];

    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('competitions.order', 'asc')->orderBy('competitions.id', 'asc');
        });
    }

    protected static array $userStaticCache = [];

    public static function primeUserCache($users): void
    {
        foreach ($users as $user) {
            static::$userStaticCache[$user->id] = $user;
        }
    }

    public static function findCachedUser(?int $id): ?User
    {
        if (! $id) {
            return null;
        }

        if (! array_key_exists($id, static::$userStaticCache)) {
            static::$userStaticCache[$id] = User::select('id', 'name', 'role', 'phone')->find($id);
        }

        return static::$userStaticCache[$id];
    }

    protected $appends = [
        'fee_display',
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
            'registration_start_at' => 'datetime',
            'registration_end_at' => 'datetime',
            'has_draw' => 'boolean',
            'show_rules' => 'boolean',
            'show_guidelines' => 'boolean',
            'show_criteria' => 'boolean',
            'is_live_score' => 'boolean',
            'has_stage_timer' => 'boolean',
            'stage_duration_minutes' => 'integer',
            'stage_warning_minutes' => 'integer',
            'stage_overtime_minutes' => 'integer',
            'stage_state' => 'array',
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
                'ganda_pa' => $gandaFee_pa,
                'ganda_pi' => $gandaFee_pi,
                // Fallbacks
                'A_tunggal' => $tunggalA_pa,
                'B_tunggal' => $tunggalB_pa,
                'C_tunggal' => $tunggalC_pa,
                'ganda' => $gandaFee_pa,
                'A' => $tunggalA_pa,
                'B' => $tunggalB_pa,
                'C' => $tunggalC_pa,
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
                'A_tunggal' => $a_pa,
                'B_tunggal' => $b_pa,
                'A' => $a_pa,
                'B' => $b_pa,
                'pa' => $a_pa,
                'pi' => $a_pi,
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
                'ganda_pa' => (int) AppSetting::get('blt_quota_ganda_pa', (int) AppSetting::get('blt_quota_ganda', 10)),
                'ganda_pi' => (int) AppSetting::get('blt_quota_ganda_pi', (int) AppSetting::get('blt_quota_ganda', 10)),
                // Fallbacks
                'A_tunggal' => (int) AppSetting::get('blt_quota_a_tunggal_pa', 16),
                'B_tunggal' => (int) AppSetting::get('blt_quota_b_tunggal_pa', 16),
                'C_tunggal' => (int) AppSetting::get('blt_quota_c_tunggal_pa', 16),
                'ganda' => (int) AppSetting::get('blt_quota_ganda_pa', 10),
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
                'A_tunggal' => (int) AppSetting::get('tmj_quota_a_tunggal_pa', $defaultQuota),
                'B_tunggal' => (int) AppSetting::get('tmj_quota_b_tunggal_pa', $defaultQuota),
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
                'ganda_pa' => (int) AppSetting::get('blt_pic_ganda_pa', $this->pic_id),
                'ganda_pi' => (int) AppSetting::get('blt_pic_ganda_pi', $this->pic_id),
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
                'pa' => (int) AppSetting::get('tmj_pic_tunggal_pa', $this->pic_id),
                'pi' => (int) AppSetting::get('tmj_pic_tunggal_pi', $this->pic_id),
            ];
        }

        return [];
    }

    public function getPicPaAttribute()
    {
        if ($this->code === 'MTQ') {
            $id = AppSetting::get('mtq_pic_pa', $this->pic_id);

            return $id ? static::findCachedUser($id) : $this->pic;
        }
        if ($this->code === 'POP') {
            $id = AppSetting::get('pop_pic_pa', $this->pic_id);

            return $id ? static::findCachedUser($id) : $this->pic;
        }
        if ($this->code === 'TMJ') {
            $id = AppSetting::get('tmj_pic_tunggal_pa', $this->pic_id);

            return $id ? static::findCachedUser($id) : $this->pic;
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

            return $id ? static::findCachedUser($id) : $this->pic;
        }
        if ($this->code === 'POP') {
            $id = AppSetting::get('pop_pic_pi', $this->pic_id);

            return $id ? static::findCachedUser($id) : $this->pic;
        }
        if ($this->code === 'TMJ') {
            $id = AppSetting::get('tmj_pic_tunggal_pi', $this->pic_id);

            return $id ? static::findCachedUser($id) : $this->pic;
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
        $prefix = strtolower($this->code);
        $id = AppSetting::get($prefix.'_pic_tunggal_pa', $this->pic_id);

        return $id ? static::findCachedUser($id) : $this->pic;
    }

    public function getPicTunggalPiAttribute()
    {
        $prefix = strtolower($this->code);
        $id = AppSetting::get($prefix.'_pic_tunggal_pi', $this->pic_id);

        return $id ? static::findCachedUser($id) : $this->pic;
    }

    public function getPicGandaPaAttribute()
    {
        $id = AppSetting::get('blt_pic_ganda_pa', $this->pic_id);

        return $id ? static::findCachedUser($id) : $this->pic;
    }

    public function getPicGandaPiAttribute()
    {
        $id = AppSetting::get('blt_pic_ganda_pi', $this->pic_id);

        return $id ? static::findCachedUser($id) : $this->pic;
    }

    public function getStatusATunggalPaAttribute(): string
    {
        $prefix = strtolower($this->code);

        return AppSetting::get($prefix.'_status_a_tunggal_pa', AppSetting::get($prefix.'_status_tunggal_pa', $this->status ?? 'buka'));
    }

    public function getStatusBTunggalPaAttribute(): string
    {
        $prefix = strtolower($this->code);

        return AppSetting::get($prefix.'_status_b_tunggal_pa', AppSetting::get($prefix.'_status_tunggal_pa', $this->status ?? 'buka'));
    }

    public function getStatusCTunggalPaAttribute(): string
    {
        $prefix = strtolower($this->code);

        return AppSetting::get($prefix.'_status_c_tunggal_pa', AppSetting::get($prefix.'_status_tunggal_pa', $this->status ?? 'buka'));
    }

    public function getStatusATunggalPiAttribute(): string
    {
        $prefix = strtolower($this->code);

        return AppSetting::get($prefix.'_status_a_tunggal_pi', AppSetting::get($prefix.'_status_tunggal_pi', $this->status ?? 'buka'));
    }

    public function getStatusBTunggalPiAttribute(): string
    {
        $prefix = strtolower($this->code);

        return AppSetting::get($prefix.'_status_b_tunggal_pi', AppSetting::get($prefix.'_status_tunggal_pi', $this->status ?? 'buka'));
    }

    public function getStatusCTunggalPiAttribute(): string
    {
        $prefix = strtolower($this->code);

        return AppSetting::get($prefix.'_status_c_tunggal_pi', AppSetting::get($prefix.'_status_tunggal_pi', $this->status ?? 'buka'));
    }

    public function getStatusTunggalPaAttribute(): string
    {
        $prefix = strtolower($this->code);

        return AppSetting::get($prefix.'_status_tunggal_pa', $this->status ?? 'buka');
    }

    public function getStatusTunggalPiAttribute(): string
    {
        $prefix = strtolower($this->code);

        return AppSetting::get($prefix.'_status_tunggal_pi', $this->status ?? 'buka');
    }

    public function getStatusGandaPaAttribute(): string
    {
        return AppSetting::get('blt_status_ganda_pa', $this->status ?? 'buka');
    }

    public function getStatusGandaPiAttribute(): string
    {
        return AppSetting::get('blt_status_ganda_pi', $this->status ?? 'buka');
    }

    public function getTierEffectiveStart(string $tierKey): ?\Carbon\Carbon
    {
        $prefix = strtolower($this->code);
        $tierStart = AppSetting::get("{$prefix}_start_{$tierKey}");
        if (! empty($tierStart) && strtotime($tierStart)) {
            return \Carbon\Carbon::parse($tierStart);
        }

        return $this->effective_registration_start;
    }

    public function getTierEffectiveEnd(string $tierKey): ?\Carbon\Carbon
    {
        $prefix = strtolower($this->code);
        $tierEnd = AppSetting::get("{$prefix}_end_{$tierKey}");
        if (! empty($tierEnd) && strtotime($tierEnd)) {
            return \Carbon\Carbon::parse($tierEnd);
        }

        return $this->effective_registration_end;
    }

    public function isTierQuotaFull(string $tierKey): bool
    {
        $prefix = strtolower($this->code);
        $tierQuotas = $this->tier_quotas;

        if ($prefix === 'blt') {
            $mappedQuotaKey = match ($tierKey) {
                'a_tunggal_pa' => 'A_tunggal_pa',
                'b_tunggal_pa' => 'B_tunggal_pa',
                'c_tunggal_pa' => 'C_tunggal_pa',
                'a_tunggal_pi' => 'A_tunggal_pi',
                'b_tunggal_pi' => 'B_tunggal_pi',
                'c_tunggal_pi' => 'C_tunggal_pi',
                'ganda_pa' => 'ganda_pa',
                'ganda_pi' => 'ganda_pi',
                default => $tierKey,
            };

            $maxQuota = (int) ($tierQuotas[$mappedQuotaKey] ?? 0);
            if ($maxQuota <= 0) {
                return false;
            }

            $regs = $this->relationLoaded('registrations')
                ? $this->registrations->filter(fn ($r) => in_array($r->status, ['pending', 'verified']))
                : $this->registrations()->with('members')->whereIn('status', ['pending', 'verified'])->get();

            $isPa = str_contains($tierKey, 'pa');
            $isGanda = str_contains($tierKey, 'ganda');

            if ($isGanda) {
                $count = $regs->filter(fn ($r) => $r->isGanda() && $r->primary_gender === ($isPa ? 'L' : 'P'))->count();
            } else {
                $kat = strtoupper(substr($tierKey, 0, 1)); // 'A', 'B', or 'C'
                $count = $regs->filter(function ($r) use ($isPa, $kat) {
                    if ($r->isGanda() || $r->primary_gender !== ($isPa ? 'L' : 'P')) {
                        return false;
                    }

                    return match ($kat) {
                        'A' => $r->isKatA(),
                        'B' => $r->isKatB(),
                        'C' => $r->isKatC(),
                        default => false,
                    };
                })->count();
            }

            return $count >= $maxQuota;
        }

        if ($prefix === 'tmj') {
            $mappedQuotaKey = match ($tierKey) {
                'a_tunggal_pa' => 'A_tunggal_pa',
                'b_tunggal_pa' => 'B_tunggal_pa',
                'a_tunggal_pi' => 'A_tunggal_pi',
                'b_tunggal_pi' => 'B_tunggal_pi',
                default => $tierKey,
            };

            $maxQuota = (int) ($tierQuotas[$mappedQuotaKey] ?? 0);
            if ($maxQuota <= 0) {
                return false;
            }

            $regs = $this->relationLoaded('registrations')
                ? $this->registrations->filter(fn ($r) => in_array($r->status, ['pending', 'verified']))
                : $this->registrations()->with('members')->whereIn('status', ['pending', 'verified'])->get();

            $isPa = str_contains($tierKey, 'pa');
            $kat = strtoupper(substr($tierKey, 0, 1)); // 'A' or 'B'

            $count = $regs->filter(function ($r) use ($isPa, $kat) {
                if ($r->primary_gender !== ($isPa ? 'L' : 'P')) {
                    return false;
                }

                return match ($kat) {
                    'A' => $r->isKatA(),
                    'B' => $r->isKatB(),
                    default => false,
                };
            })->count();

            return $count >= $maxQuota;
        }

        return false;
    }

    public function getTierRegistrationStatusInfo(string $tierKey): array
    {
        $prefix = strtolower($this->code);

        // 1. Manual status override for this tier / competition
        $tierStatus = AppSetting::get("{$prefix}_status_{$tierKey}", $this->status ?? 'buka');
        $effectiveEnd = $this->getTierEffectiveEnd($tierKey);
        $hasCustomDeadline = ! empty(AppSetting::get("{$prefix}_end_{$tierKey}")) || ! empty($this->registration_end_at);

        if ($this->status === 'tutup' || $tierStatus === 'tutup') {
            return [
                'is_open' => false,
                'status_code' => 'closed_manual',
                'status_label' => 'Tutup',
                'status_color' => 'rose',
                'badge_class' => 'bg-rose-500/15 text-rose-400 border border-rose-500/30',
                'deadline' => $effectiveEnd,
                'has_custom_deadline' => $hasCustomDeadline,
            ];
        }

        if ($this->status === 'selesai' || $tierStatus === 'selesai') {
            return [
                'is_open' => false,
                'status_code' => 'finished',
                'status_label' => 'Selesai',
                'status_color' => 'slate',
                'badge_class' => 'bg-white/[0.05] text-slate-400 border border-white/[0.08]',
                'deadline' => $effectiveEnd,
                'has_custom_deadline' => $hasCustomDeadline,
            ];
        }

        // 2. Global application status
        $globalStatus = AppSetting::get('global_registration_status', 'open');
        if ($globalStatus === 'closed') {
            return [
                'is_open' => false,
                'status_code' => 'closed_global',
                'status_label' => 'Tutup',
                'status_color' => 'rose',
                'badge_class' => 'bg-rose-500/15 text-rose-400 border border-rose-500/30',
                'deadline' => $effectiveEnd,
                'has_custom_deadline' => $hasCustomDeadline,
            ];
        }

        // 3. Date & Deadline Check for this tier
        $now = now();
        $autoClose = AppSetting::get('registration_auto_close', '1') == '1';
        $startDate = $this->getTierEffectiveStart($tierKey);

        if ($autoClose && $startDate && $now->lt($startDate)) {
            return [
                'is_open' => false,
                'status_code' => 'not_started',
                'status_label' => 'Belum Buka',
                'status_color' => 'amber',
                'badge_class' => 'bg-amber-500/15 text-amber-300 border border-amber-500/30',
                'deadline' => $effectiveEnd,
                'has_custom_deadline' => $hasCustomDeadline,
            ];
        }

        if ($autoClose && $effectiveEnd && $now->gt($effectiveEnd)) {
            return [
                'is_open' => false,
                'status_code' => 'closed_expired',
                'status_label' => 'Berakhir',
                'status_color' => 'rose',
                'badge_class' => 'bg-rose-500/15 text-rose-400 border border-rose-500/30',
                'deadline' => $effectiveEnd,
                'has_custom_deadline' => $hasCustomDeadline,
            ];
        }

        // 4. Quota check for this tier
        if ($this->isTierQuotaFull($tierKey)) {
            return [
                'is_open' => false,
                'status_code' => 'closed_quota',
                'status_label' => 'Penuh',
                'status_color' => 'purple',
                'badge_class' => 'bg-purple-500/15 text-purple-400 border border-purple-500/30',
                'deadline' => $effectiveEnd,
                'has_custom_deadline' => $hasCustomDeadline,
            ];
        }

        // 5. Open / Aktif
        return [
            'is_open' => true,
            'status_code' => 'open',
            'status_label' => 'Buka',
            'status_color' => 'emerald',
            'badge_class' => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30',
            'deadline' => $effectiveEnd,
            'has_custom_deadline' => $hasCustomDeadline,
        ];
    }

    public function getGuidelinesEmbedUrlAttribute(): ?string
    {
        $val = trim($this->guidelines_file ?? '');
        if (! $val) {
            return null;
        }

        // If full iframe snippet, extract src
        if (preg_match('/src=["\']([^"\']+)["\']/', $val, $matches)) {
            $val = $matches[1];
        }

        // 1. Google Drive File: /file/d/{ID}/..., /file/u/0/d/{ID}/..., open?id={ID}, uc?id={ID}
        if (preg_match('/drive\.google\.com\/(?:file\/(?:u\/\d+\/)?d\/|open\?id=|uc\?(?:[^&]*&)*id=)([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/preview";
        }

        // 2. Google Drive Folder: /drive/folders/{ID} or /drive/u/0/folders/{ID}
        if (preg_match('/drive\.google\.com\/drive\/(?:u\/\d+\/)?folders\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://drive.google.com/embeddedfolderview?id={$matches[1]}#list";
        }

        // 3. Google Docs link: /document/d/{ID}/...
        if (preg_match('/docs\.google\.com\/document\/(?:u\/\d+\/)?d\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://docs.google.com/document/d/{$matches[1]}/preview";
        }

        // 4. Google Sheets link: /spreadsheets/d/{ID}/...
        if (preg_match('/docs\.google\.com\/spreadsheets\/(?:u\/\d+\/)?d\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://docs.google.com/spreadsheets/d/{$matches[1]}/preview";
        }

        // 5. Google Slides / Presentations: /presentation/d/{ID}/...
        if (preg_match('/docs\.google\.com\/presentation\/(?:u\/\d+\/)?d\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://docs.google.com/presentation/d/{$matches[1]}/preview";
        }

        // 6. Canva link
        if (preg_match('/canva\.com\/design\/([a-zA-Z0-9_-]+)(?:\/([a-zA-Z0-9_-]+))?/', $val, $matches)) {
            $designId = $matches[1];
            $secondPart = $matches[2] ?? '';
            if (in_array(strtolower($secondPart), ['view', 'watch', 'edit', 'embed', 'preview', ''])) {
                return "https://www.canva.com/design/{$designId}/view?embed";
            }
            return "https://www.canva.com/design/{$designId}/{$secondPart}/view?embed";
        }

        // 7. Local uploaded file path
        if (! str_starts_with($val, 'http://') && ! str_starts_with($val, 'https://')) {
            return asset('storage/'.ltrim($val, '/'));
        }

        return $val;
    }

    public function getGuidelinesDownloadUrlAttribute(): ?string
    {
        $val = trim($this->guidelines_file ?? '');
        if (! $val) {
            return null;
        }

        // If full iframe snippet, extract src
        if (preg_match('/src=["\']([^"\']+)["\']/', $val, $matches)) {
            $val = $matches[1];
        }

        // 1. Google Drive File: /file/d/{ID}/view
        if (preg_match('/drive\.google\.com\/(?:file\/(?:u\/\d+\/)?d\/|open\?id=|uc\?(?:[^&]*&)*id=)([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/view?usp=sharing";
        }

        // 2. Google Drive Folder: /drive/folders/{ID}
        if (preg_match('/drive\.google\.com\/drive\/(?:u\/\d+\/)?folders\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://drive.google.com/drive/folders/{$matches[1]}?usp=sharing";
        }

        // 3. Google Docs link
        if (preg_match('/docs\.google\.com\/document\/(?:u\/\d+\/)?d\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://docs.google.com/document/d/{$matches[1]}/edit?usp=sharing";
        }

        // 4. Google Sheets link
        if (preg_match('/docs\.google\.com\/spreadsheets\/(?:u\/\d+\/)?d\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://docs.google.com/spreadsheets/d/{$matches[1]}/edit?usp=sharing";
        }

        // 5. Google Slides link
        if (preg_match('/docs\.google\.com\/presentation\/(?:u\/\d+\/)?d\/([a-zA-Z0-9_-]+)/', $val, $matches)) {
            return "https://docs.google.com/presentation/d/{$matches[1]}/edit?usp=sharing";
        }

        // 6. Canva link
        if (preg_match('/canva\.com\/design\/([a-zA-Z0-9_-]+)(?:\/([a-zA-Z0-9_-]+))?/', $val, $matches)) {
            $designId = $matches[1];
            $secondPart = $matches[2] ?? '';
            if (in_array(strtolower($secondPart), ['view', 'watch', 'edit', 'embed', 'preview', ''])) {
                return "https://www.canva.com/design/{$designId}/view";
            }
            return "https://www.canva.com/design/{$designId}/{$secondPart}/view";
        }

        // 7. Local uploaded file path
        if (! str_starts_with($val, 'http://') && ! str_starts_with($val, 'https://')) {
            return asset('storage/'.ltrim($val, '/'));
        }

        return $val;
    }

    public function getFeeDisplayAttribute(): string
    {
        if ($this->code === 'BLT') {
            $tiers = $this->tier_fees;
            $fees = array_values(array_filter([
                $tiers['A_tunggal_pa'] ?? null,
                $tiers['B_tunggal_pa'] ?? null,
                $tiers['C_tunggal_pa'] ?? null,
                $tiers['A_tunggal_pi'] ?? null,
                $tiers['B_tunggal_pi'] ?? null,
                $tiers['C_tunggal_pi'] ?? null,
                $tiers['ganda_pa'] ?? null,
                $tiers['ganda_pi'] ?? null,
            ]));

            if (! empty($fees)) {
                $min = min($fees);
                $max = max($fees);
                if ($min == $max) {
                    return $min > 0 ? 'Rp '.number_format($min, 0, ',', '.') : 'GRATIS';
                }

                return 'Rp '.number_format($min, 0, ',', '.').' – Rp '.number_format($max, 0, ',', '.').' (Tunggal & Ganda)';
            }
        }

        if ($this->code === 'TMJ') {
            $tiers = $this->tier_fees;
            $fees = array_values(array_filter([
                $tiers['A_tunggal_pa'] ?? null,
                $tiers['B_tunggal_pa'] ?? null,
                $tiers['A_tunggal_pi'] ?? null,
                $tiers['B_tunggal_pi'] ?? null,
            ]));

            if (! empty($fees)) {
                $min = min($fees);
                $max = max($fees);
                if ($min == $max) {
                    return $min > 0 ? 'Rp '.number_format($min, 0, ',', '.') : 'GRATIS';
                }

                return 'Rp '.number_format($min, 0, ',', '.').' – Rp '.number_format($max, 0, ',', '.').' (Kat A & B)';
            }
        }

        if (in_array($this->code, ['MTQ', 'POP'])) {
            $prefix = strtolower($this->code);
            $fPa = (float) AppSetting::get($prefix.'_fee_pa', $this->registration_fee);
            $fPi = (float) AppSetting::get($prefix.'_fee_pi', $this->registration_fee);

            if ($fPa == $fPi) {
                return $fPa > 0 ? 'Rp '.number_format($fPa, 0, ',', '.') : 'GRATIS';
            }

            return 'Rp '.number_format($fPa, 0, ',', '.').' (PA) / Rp '.number_format($fPi, 0, ',', '.').' (PI)';
        }

        return ((float) $this->registration_fee) > 0 ? 'Rp '.number_format((float) $this->registration_fee, 0, ',', '.') : 'GRATIS';
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

    public function pics(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'competition_pics', 'competition_id', 'user_id')
            ->withPivot('role_title')
            ->withTimestamps();
    }

    /**
     * Get all unique PICs assigned to this competition:
     * - Primary PIC from `pic_id`
     * - Multi-PICs from `competition_pics` pivot table
     * - Sector PICs from `AppSetting` (if BLT, TMJ, MTQ, POP)
     */
    public function getAllPicsAttribute()
    {
        $users = collect();
        if ($this->pic) {
            $users->push($this->pic);
        }
        foreach ($this->pics as $p) {
            $users->push($p);
        }
        if (! empty($this->tier_pics)) {
            foreach ($this->tier_pics as $sectorPicId) {
                if ($sectorPicId && $secUser = static::findCachedUser($sectorPicId)) {
                    $users->push($secUser);
                }
            }
        }

        return $users->unique('id')->values();
    }

    /**
     * Is WhatsApp notification enabled for PIC when new participant registers?
     */
    public function getNotifyPicAttribute(): bool
    {
        $val = AppSetting::get('competition_notify_pic_'.$this->id, '1');

        return $val === '1' || $val === true || $val === 'true' || $val === 1;
    }

    /**
     * Additional assistant phone numbers (comma-separated string)
     */
    public function getAssistantPhonesAttribute(): string
    {
        return (string) AppSetting::get('competition_assistant_phones_'.$this->id, '');
    }

    /**
     * Get all valid phone numbers of assigned PICs formatted for WhatsApp.
     * Returns empty array if notifications for this competition are switched OFF.
     */
    public function getAllPicPhonesAttribute(): array
    {
        if (! $this->notify_pic) {
            return [];
        }

        $phones = collect();

        // 1. Primary PIC & Sector PICs
        foreach ($this->all_pics as $picUser) {
            if (! empty($picUser->phone)) {
                $phones->push($picUser->phone);
            }
        }

        // 2. Assistant / Co-PIC Phones (from comma-separated setting)
        if (! empty($this->assistant_phones)) {
            $rawList = explode(',', $this->assistant_phones);
            foreach ($rawList as $raw) {
                $trimmed = trim($raw);
                if (! empty($trimmed)) {
                    $phones->push($trimmed);
                }
            }
        }

        return $phones
            ->map(function ($phone) {
                $clean = preg_replace('/[^0-9]/', '', (string) $phone);
                if (str_starts_with($clean, '0')) {
                    $clean = '62'.substr($clean, 1);
                } elseif (str_starts_with($clean, '8')) {
                    $clean = '628'.substr($clean, 1);
                }

                return $clean;
            })
            ->filter(fn ($p) => strlen($p) >= 9)
            ->unique()
            ->values()
            ->toArray();
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

    public function getQuotaDisplayAttribute(): string
    {
        $unitWord = match (strtolower($this->type)) {
            'regu' => 'Regu',
            'tim' => 'Tim',
            'kelompok' => 'Kelompok',
            'pasangan' => 'Pasangan',
            default => 'Peserta',
        };

        if ($this->code === 'BLT') {
            $tunggalTotal = ($this->tier_quotas['A_tunggal_pa'] ?? 16)
                          + ($this->tier_quotas['B_tunggal_pa'] ?? 16)
                          + ($this->tier_quotas['C_tunggal_pa'] ?? 32)
                          + ($this->tier_quotas['A_tunggal_pi'] ?? 16)
                          + ($this->tier_quotas['B_tunggal_pi'] ?? 16)
                          + ($this->tier_quotas['C_tunggal_pi'] ?? 16);
            $gandaPa = $this->tier_quotas['ganda_pa'] ?? 0;
            $gandaPi = $this->tier_quotas['ganda_pi'] ?? 0;
            $gandaText = ($gandaPa <= 0 && $gandaPi <= 0) ? '∞ Bebas Ganda' : (($gandaPa + $gandaPi).' Ganda');

            return "{$tunggalTotal} Tunggal / {$gandaText}";
        }

        if (in_array($this->code, ['MTQ', 'POP'])) {
            return "{$this->quota} Peserta (Gabungan PA & PI)";
        }

        if ($this->code === 'TMJ') {
            $total = ($this->tier_quotas['A_tunggal_pa'] ?? 10)
                   + ($this->tier_quotas['B_tunggal_pa'] ?? 10)
                   + ($this->tier_quotas['A_tunggal_pi'] ?? 10)
                   + ($this->tier_quotas['B_tunggal_pi'] ?? 10);

            return "{$total} Peserta (Tunggal Kat A & B)";
        }

        if ($this->isUnlimitedQuota()) {
            return '∞ Tak Terbatas';
        }

        return "{$this->quota} {$unitWord}";
    }

    /**
     * Get list of optional songs for Pop Singer / stage competitions
     */
    public function getSongOptionsAttribute(): array
    {
        $raw = $this->raw_song_options;
        if (empty($raw)) {
            return [];
        }

        $lines = explode("\n", str_replace("\r", "", $raw));
        $songs = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (! empty($trimmed)) {
                // Remove numbering like "1. " or "1) " if user typed it
                $cleanTitle = preg_replace('/^\d+[\.\)]\s*/', '', $trimmed);
                $songs[] = trim($cleanTitle);
            }
        }

        return array_values(array_unique(array_filter($songs)));
    }

    /**
     * Get raw text of song options (1 line per song)
     */
    public function getRawSongOptionsAttribute(): string
    {
        $defaultSongs = "Deen Assalam\nRahmatun Lil'Alameen\nYa Maulana\nMan Ana\nAisyah Istri Rasulullah\nBidadari Surga\nSholawat Cinta\nKisah Sang Rasul";

        return AppSetting::get('competition_songs_'.$this->id)
            ?: (AppSetting::get('pop_song_options') ?: $defaultSongs);
    }

    /**
     * Effective registration start date: custom if filled, otherwise global app setting
     */
    public function getEffectiveRegistrationStartAttribute(): ?\Carbon\Carbon
    {
        if ($this->registration_start_at) {
            return \Carbon\Carbon::parse($this->registration_start_at);
        }

        $globalStart = AppSetting::get('registration_start_date');
        if (! empty($globalStart) && strtotime($globalStart)) {
            return \Carbon\Carbon::parse($globalStart);
        }

        return null;
    }

    /**
     * Effective registration deadline: custom if filled, otherwise global app setting
     */
    public function getEffectiveRegistrationEndAttribute(): ?\Carbon\Carbon
    {
        if ($this->registration_end_at) {
            return \Carbon\Carbon::parse($this->registration_end_at);
        }

        $globalDeadline = AppSetting::get('registration_deadline');
        if (! empty($globalDeadline) && strtotime($globalDeadline)) {
            $parsed = \Carbon\Carbon::parse($globalDeadline);
            if (strlen(trim($globalDeadline)) === 10 && !str_contains($globalDeadline, ':') && !str_contains($globalDeadline, 'T')) {
                $parsed = $parsed->endOfDay();
            }
            return $parsed;
        }

        return null;
    }

    /**
     * Formatted string of effective deadline
     */
    public function getDeadlineDisplayAttribute(): string
    {
        $deadline = $this->effective_registration_end;
        if (! $deadline) {
            return '-';
        }

        return $deadline->translatedFormat('d F Y, H:i').' WIB';
    }

    /**
     * Check if total active registrations for this competition has reached quota
     */
    public function getIsQuotaFullAttribute(): bool
    {
        if ($this->isUnlimitedQuota()) {
            return false;
        }

        $activeCount = $this->relationLoaded('registrations')
            ? $this->registrations->whereIn('status', ['pending', 'verified'])->count()
            : $this->registrations()->whereIn('status', ['pending', 'verified'])->count();

        return $activeCount >= (int) $this->quota;
    }

    /**
     * Check if current time has passed the effective deadline
     */
    public function getIsRegistrationExpiredAttribute(): bool
    {
        $deadline = $this->effective_registration_end;
        if (! $deadline) {
            return false;
        }

        $autoClose = AppSetting::get('registration_auto_close', '1') == '1';
        if (! $autoClose) {
            return false;
        }

        return now()->gt($deadline);
    }

    /**
     * Get comprehensive registration status info for UI badges, buttons, and validation
     */
    public function getRegistrationStatusInfoAttribute(): array
    {
        $startDate = $this->effective_registration_start;
        $deadline = $this->effective_registration_end;
        $formattedStart = $startDate ? $startDate->translatedFormat('d F Y, H:i').' WIB' : null;

        // 1. Manual status override per competition (Highest Priority)
        if ($this->status === 'tutup') {
            return [
                'is_open' => false,
                'status_code' => 'closed_manual',
                'status_label' => 'Ditutup Manual',
                'status_color' => 'rose',
                'badge_class' => 'bg-rose-500/15 text-rose-400 border border-rose-500/30',
                'button_text' => 'Pendaftaran Ditutup',
                'button_icon' => 'lock',
                'message' => 'Pendaftaran untuk cabang lomba '.$this->name.' telah ditutup oleh panitia.',
                'deadline_formatted' => $this->deadline_display,
                'start_date_formatted' => $formattedStart,
            ];
        }

        if ($this->status === 'selesai') {
            return [
                'is_open' => false,
                'status_code' => 'finished',
                'status_label' => 'Lomba Selesai',
                'status_color' => 'slate',
                'badge_class' => 'bg-white/[0.05] text-slate-400 border border-white/[0.08]',
                'button_text' => 'Lomba Selesai',
                'button_icon' => 'check-circle-2',
                'message' => 'Perlombaan cabang '.$this->name.' telah selesai dilaksanakan.',
                'deadline_formatted' => $this->deadline_display,
                'start_date_formatted' => $formattedStart,
            ];
        }

        // 2. Global application switch
        $globalStatus = AppSetting::get('global_registration_status', 'open');
        if ($globalStatus === 'closed') {
            return [
                'is_open' => false,
                'status_code' => 'closed_global',
                'status_label' => 'Ditutup (Event Selesai)',
                'status_color' => 'rose',
                'badge_class' => 'bg-rose-500/15 text-rose-400 border border-rose-500/30',
                'button_text' => 'Pendaftaran Ditutup',
                'button_icon' => 'lock',
                'message' => AppSetting::get('registration_closed_message', 'Pendaftaran TALENTA 2026 telah resmi ditutup.'),
                'deadline_formatted' => $this->deadline_display,
                'start_date_formatted' => $formattedStart,
            ];
        }

        // 3. Date & Deadline Check
        $now = now();
        $autoClose = AppSetting::get('registration_auto_close', '1') == '1';

        if ($autoClose && $startDate && $now->lt($startDate)) {
            return [
                'is_open' => false,
                'status_code' => 'not_started',
                'status_label' => 'Belum Dibuka',
                'status_color' => 'amber',
                'badge_class' => 'bg-amber-500/15 text-amber-300 border border-amber-500/30',
                'button_text' => 'Belum Dibuka',
                'button_icon' => 'clock',
                'message' => 'Pendaftaran untuk cabang lomba '.$this->name.' baru dibuka mulai '.$startDate->translatedFormat('d F Y, H:i').' WIB.',
                'deadline_formatted' => $this->deadline_display,
                'start_date_formatted' => $formattedStart,
            ];
        }

        if ($autoClose && $deadline && $now->gt($deadline)) {
            return [
                'is_open' => false,
                'status_code' => 'closed_expired',
                'status_label' => 'Batas Waktu Berakhir',
                'status_color' => 'rose',
                'badge_class' => 'bg-rose-500/15 text-rose-400 border border-rose-500/30',
                'button_text' => 'Pendaftaran Ditutup',
                'button_icon' => 'lock',
                'message' => 'Pendaftaran untuk cabang lomba '.$this->name.' telah berakhir pada '.$deadline->translatedFormat('d F Y, H:i').' WIB.',
                'deadline_formatted' => $this->deadline_display,
                'start_date_formatted' => $formattedStart,
            ];
        }

        // 4. Quota Check
        if ($this->is_quota_full) {
            return [
                'is_open' => false,
                'status_code' => 'closed_quota',
                'status_label' => 'Kuota Penuh',
                'status_color' => 'purple',
                'badge_class' => 'bg-purple-500/15 text-purple-400 border border-purple-500/30',
                'button_text' => 'Kuota Terpenuhi',
                'button_icon' => 'users',
                'message' => 'Mohon maaf, kuota pendaftaran untuk cabang lomba '.$this->name.' telah terpenuhi ('.$this->quota.' peserta).',
                'deadline_formatted' => $this->deadline_display,
                'start_date_formatted' => $formattedStart,
            ];
        }

        // 5. Open / Aktif
        return [
            'is_open' => true,
            'status_code' => 'open',
            'status_label' => 'Pendaftaran Dibuka',
            'status_color' => 'emerald',
            'badge_class' => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30',
            'button_text' => 'Daftar Cabang Ini',
            'button_icon' => 'arrow-right',
            'message' => 'Pendaftaran dibuka.',
            'deadline_formatted' => $this->deadline_display,
            'start_date_formatted' => $formattedStart,
        ];
    }
}
