<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'registration_id',
        'judge_id',
        'total_score',
        'is_locked',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'decimal:2',
            'is_locked' => 'boolean',
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

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ScoreDetail::class);
    }
}
