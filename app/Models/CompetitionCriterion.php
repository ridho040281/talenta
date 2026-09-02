<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionCriterion extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'name',
        'weight_percentage',
        'min_score',
        'max_score',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'weight_percentage' => 'integer',
            'min_score' => 'decimal:2',
            'max_score' => 'decimal:2',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function scoreDetails(): HasMany
    {
        return $this->hasMany(ScoreDetail::class, 'criterion_id');
    }
}
