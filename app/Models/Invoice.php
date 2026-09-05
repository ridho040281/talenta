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

    public function recalculateTotals(): void
    {
        $subtotal = 0;
        foreach ($this->registrations as $reg) {
            $subtotal += (float) $reg->fee;
        }
        $this->unique_code = 0;
        $this->total_amount = $subtotal;
        $this->final_amount = $subtotal;
        $this->save();
    }
}
