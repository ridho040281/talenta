<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'type',
        'total_amount',
        'unique_code',
        'final_amount',
        'payment_proof',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'unique_code' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedFinalAmountAttribute(): string
    {
        return 'Rp '.number_format($this->final_amount, 0, ',', '.');
    }

    public function getBonusDiscountAttribute(): float
    {
        if ((float) $this->total_amount > (float) $this->final_amount) {
            return (float) ($this->total_amount - $this->final_amount);
        }

        return 0;
    }

    public function getHasBonusAttribute(): bool
    {
        return $this->bonus_discount > 0;
    }

    public function recalculateTotals(): void
    {
        $this->load(['registrations.competition', 'registrations.members']);

        $subtotal = 0;
        $compCounts = [];
        $compFees = [];

        foreach ($this->registrations as $reg) {
            $regFee = (float) $reg->fee;
            $subtotal += $regFee;
            if ($reg->competition) {
                $code = $reg->competition->code;
                $compCounts[$code] = ($compCounts[$code] ?? 0) + 1;
                $compFees[$code] = $regFee ?: (float) $reg->competition->registration_fee;
            }
        }

        $totalBonusDiscount = 0;
        foreach ($compCounts as $code => $count) {
            $isBonusActive = ($code === 'MIPA') || (AppSetting::get('bonus_active_'.strtolower($code), '0') === '1');
            $minQuota = (int) AppSetting::get('bonus_min_'.strtolower($code), 10);
            $freeCountPerBatch = (int) AppSetting::get('bonus_free_'.strtolower($code), 1);

            if ($isBonusActive && $minQuota > 0 && $count >= $minQuota) {
                $freeCount = (int) (floor($count / $minQuota) * $freeCountPerBatch);
                $unitFee = $compFees[$code] ?? 50000;
                $totalBonusDiscount += ($freeCount * $unitFee);
            }
        }

        $this->unique_code = 0;
        $this->total_amount = $subtotal;
        $this->final_amount = max(0, $subtotal - $totalBonusDiscount);
        $this->save();
    }
}
