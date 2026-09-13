<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_type',
        'reference_id',
        'adjustment_type',
        'amount',
        'bank_account',
        'proof_file',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTargetModel()
    {
        if ($this->reference_type === 'invoice') {
            return Invoice::find($this->reference_id);
        }

        return Registration::find($this->reference_id);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->adjustment_type) {
            'refund_overpayment' => 'Kelebihan Transfer (Refund)',
            'refund_cancellation' => 'Batal Ikut (Refund Penuh)',
            'discount' => 'Diskon / Keringanan',
            'correction' => 'Koreksi Pembukuan',
            default => 'Penyesuaian Kas',
        };
    }
}
