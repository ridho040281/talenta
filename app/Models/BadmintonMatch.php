<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadmintonMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'court_number',
        'match_code',
        'round_name',
        'category',
        'match_type',
        'team1_registration_id',
        'team1_school',
        'team1_player1',
        'team1_player2',
        'team2_registration_id',
        'team2_school',
        'team2_player1',
        'team2_player2',
        'current_set',
        'team1_set1',
        'team2_set1',
        'team1_set2',
        'team2_set2',
        'team1_set3',
        'team2_set3',
        'server_team',
        'server_player',
        'match_status',
        'winner_team',
        'umpire_id',
        'started_at',
        'finished_at',
        'scores_history',
    ];

    protected function casts(): array
    {
        return [
            'scores_history' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'current_set' => 'integer',
            'server_team' => 'integer',
            'server_player' => 'integer',
            'winner_team' => 'integer',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function team1Registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'team1_registration_id');
    }

    public function team2Registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'team2_registration_id');
    }

    public function umpire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'umpire_id');
    }

    public function getTeam1DisplayNameAttribute(): string
    {
        if ($this->match_type === 'double' && $this->team1_player2) {
            return "{$this->team1_player1} / {$this->team1_player2}";
        }

        return $this->team1_player1;
    }

    public function getTeam2DisplayNameAttribute(): string
    {
        if ($this->match_type === 'double' && $this->team2_player2) {
            return "{$this->team2_player1} / {$this->team2_player2}";
        }

        return $this->team2_player1;
    }

    public function getCurrentSetScores(): array
    {
        return match ($this->current_set) {
            1 => ['t1' => $this->team1_set1, 't2' => $this->team2_set1],
            2 => ['t1' => $this->team1_set2, 't2' => $this->team2_set2],
            3 => ['t1' => $this->team1_set3, 't2' => $this->team2_set3],
            default => ['t1' => 0, 't2' => 0],
        };
    }

    public function getSetsWon(): array
    {
        $w1 = 0;
        $w2 = 0;

        // Set 1
        if ((($this->team1_set1 >= 21 || $this->team2_set1 >= 21) && abs($this->team1_set1 - $this->team2_set1) >= 2) || max($this->team1_set1, $this->team2_set1) === 30) {
            if ($this->team1_set1 > $this->team2_set1) {
                $w1++;
            } elseif ($this->team2_set1 > $this->team1_set1) {
                $w2++;
            }
        }

        // Set 2
        if ((($this->team1_set2 >= 21 || $this->team2_set2 >= 21) && abs($this->team1_set2 - $this->team2_set2) >= 2) || max($this->team1_set2, $this->team2_set2) === 30) {
            if ($this->team1_set2 > $this->team2_set2) {
                $w1++;
            } elseif ($this->team2_set2 > $this->team1_set2) {
                $w2++;
            }
        }

        // Set 3
        if ((($this->team1_set3 >= 21 || $this->team2_set3 >= 21) && abs($this->team1_set3 - $this->team2_set3) >= 2) || max($this->team1_set3, $this->team2_set3) === 30) {
            if ($this->team1_set3 > $this->team2_set3) {
                $w1++;
            } elseif ($this->team2_set3 > $this->team1_set3) {
                $w2++;
            }
        }

        return ['t1' => $w1, 't2' => $w2];
    }
}
