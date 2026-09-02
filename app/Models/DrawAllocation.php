<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'registration_id',
        'draw_number',
        'spun_at',
        'spun_by',
    ];

    protected function casts(): array
    {
        return [
            'spun_at' => 'datetime',
            'draw_number' => 'integer',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function spinner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'spun_by');
    }
}
