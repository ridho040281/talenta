<?php

namespace App\Models;

use Carbon\Carbon;
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
                'text' => '',
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
            'teks_1' => [
                'text' => '',
                'top' => 35,
                'left' => 50,
                'size' => 16,
                'color' => '#1e293b',
                'bold' => false,
                'align' => 'center',
                'font' => 'sans',
                'visible' => false,
            ],
            'teks_2' => [
                'text' => '',
                'top' => 57,
                'left' => 50,
                'size' => 15,
                'color' => '#1e293b',
                'bold' => false,
                'align' => 'center',
                'font' => 'sans',
                'visible' => false,
            ],
            'teks_3' => [
                'text' => '',
                'top' => 71,
                'left' => 50,
                'size' => 14,
                'color' => '#1e293b',
                'bold' => false,
                'align' => 'center',
                'font' => 'sans',
                'visible' => false,
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

    /**
     * Format a certificate number based on template rules
     */
    public function formatNumber(string|int $seq, ?Carbon $date = null): string
    {
        $date = $date ?: Carbon::now();
        $seqStr = is_numeric($seq) ? str_pad((string) $seq, 3, '0', STR_PAD_LEFT) : (string) $seq;

        $monthRoman = match ((int) $date->format('m')) {
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        };

        $rawFormat = ! empty(trim($this->number_format ?? ''))
            ? trim($this->number_format)
            : '[NO]/TALENTA/MTsN1-BLT/[MONTH]/[YEAR]';

        return str_ireplace(
            ['[NO]', '[MONTH]', '[YEAR]'],
            [$seqStr, $monthRoman, $date->format('Y')],
            $rawFormat
        );
    }
}
