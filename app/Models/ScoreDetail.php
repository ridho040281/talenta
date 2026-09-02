<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'score_id',
        'criterion_id',
        'score_value',
    ];

    protected function casts(): array
    {
        return [
            'score_value' => 'decimal:2',
        ];
    }

    public function score(): BelongsTo
    {
        return $this->belongsTo(Score::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(CompetitionCriterion::class, 'criterion_id');
    }
}
