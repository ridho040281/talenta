<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event',
        'username_attempt',
        'description',
        'status',
        'ip_address',
        'user_agent',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to quickly record an activity log
     */
    public static function record(
        string $event,
        string $description,
        ?User $user = null,
        string $status = 'success',
        ?string $usernameAttempt = null,
        array $properties = []
    ): self {
        $ip = Request::ip() ?? '127.0.0.1';
        $agent = Request::userAgent() ?? 'Unknown Agent';

        return self::create([
            'user_id' => $user?->id,
            'event' => $event,
            'username_attempt' => $usernameAttempt ?: ($user?->name ?? ($user?->email ?: $user?->nisn)),
            'description' => $description,
            'status' => $status,
            'ip_address' => $ip,
            'user_agent' => $agent,
            'properties' => !empty($properties) ? $properties : null,
        ]);
    }
}
