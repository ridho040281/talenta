<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'competition_id',
        'background_path',
        'layout_config',
        'number_format',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'layout_config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Default layout configuration for coordinates and typography (percentages relative to A4 landscape)
     */
    public static function defaultLayoutConfig(): array
    {
        return [
            'nomor' => [
                'top' => 25,
                'left' => 50,
                'size' => 14,
                'color' => '#1e293b',
                'bold' => false,
                'align' => 'center',
                'font' => 'sans',
                'visible' => true,
            ],
            'nama' => [
                'top' => 43,
                'left' => 50,
                'size' => 32,
                'color' => '#0f172a',
                'bold' => true,
                'align' => 'center',
                'font' => 'serif',
                'visible' => true,
            ],
            'sekolah' => [
                'top' => 53,
                'left' => 50,
                'size' => 18,
                'color' => '#334155',
                'bold' => false,
                'align' => 'center',
                'font' => 'sans',
                'visible' => true,
            ],
            'predikat' => [
                'top' => 61,
                'left' => 50,
                'size' => 22,
                'color' => '#b45309',
                'bold' => true,
                'align' => 'center',
                'font' => 'sans',
                'visible' => true,
            ],
            'lomba' => [
                'top' => 68,
                'left' => 50,
                'size' => 18,
                'color' => '#1e293b',
                'bold' => true,
                'align' => 'center',
                'font' => 'sans',
                'visible' => true,
            ],
            'tanggal' => [
                'top' => 79,
                'left' => 75,
                'size' => 14,
                'color' => '#334155',
                'bold' => false,
                'align' => 'center',
                'font' => 'sans',
                'visible' => true,
            ],
            'qrcode' => [
                'top' => 75,
                'left' => 15,
                'size' => 75,
                'visible' => true,
            ],
        ];
    }

    /**
     * Merge stored config with defaults to ensure all keys exist
     */
    public function getEffectiveLayoutAttribute(): array
    {
        $defaults = self::defaultLayoutConfig();
        $stored = $this->layout_config ?: [];

        $merged = [];
        foreach ($defaults as $key => $defaultValues) {
            $merged[$key] = array_merge($defaultValues, $stored[$key] ?? []);
        }

        return $merged;
    }
}
