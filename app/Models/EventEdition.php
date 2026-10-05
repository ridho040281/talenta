<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class EventEdition extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'event_name',
        'theme_slogan',
        'is_active',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Ensure default editions (2026, 2027, 2028) exist in the database.
     */
    public static function ensureDefaultEditions(): void
    {
        try {
            if (! Schema::hasTable('event_editions')) {
                return;
            }

            $defaults = [
                ['year' => '2026', 'event_name' => 'Milad ke-57 MTsN 1 Blitar'],
                ['year' => '2027', 'event_name' => 'Milad ke-58 MTsN 1 Blitar'],
                ['year' => '2028', 'event_name' => 'Milad ke-59 MTsN 1 Blitar'],
            ];

            $hasActive = static::where('is_active', true)->exists();

            foreach ($defaults as $def) {
                if (! static::where('year', $def['year'])->exists()) {
                    static::create([
                        'year' => $def['year'],
                        'event_name' => $def['event_name'],
                        'is_active' => (! $hasActive && $def['year'] === '2026'),
                        'status' => 'open',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore during migration bootstrap
        }
    }

    /**
     * Get the active event edition record.
     */
    public static function getActiveEdition(): ?self
    {
        return Cache::remember('active_event_edition', 3600, function () {
            try {
                return static::where('is_active', true)->first()
                    ?? static::where('year', AppSetting::get('event_year', '2026'))->first()
                    ?? static::orderBy('year', 'asc')->first();
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /**
     * Get the currently active event year (e.g. '2026', '2027').
     */
    public static function getActiveYear(): string
    {
        $edition = static::getActiveEdition();

        return (string) ($edition?->year ?? AppSetting::get('event_year', '2026'));
    }

    /**
     * Get the currently active event name (e.g. 'Milad ke-57 MTsN 1 Blitar').
     */
    public static function getActiveEventName(): string
    {
        $edition = static::getActiveEdition();

        return (string) ($edition?->event_name ?? AppSetting::get('event_name', 'Milad ke-57 MTsN 1 Blitar'));
    }

    /**
     * Get all available editions ordered by year ascending (e.g. 2026, 2027, 2028).
     */
    public static function getAvailableEditions(): Collection
    {
        try {
            $editions = static::orderBy('year', 'asc')->get();
            if ($editions->isEmpty() && Schema::hasTable('event_editions')) {
                static::ensureDefaultEditions();
                $editions = static::orderBy('year', 'asc')->get();
            }

            return $editions;
        } catch (\Throwable $e) {
            return new Collection;
        }
    }

    /**
     * Activate a specific year edition.
     */
    public static function activateEdition(string $year): self
    {
        Cache::forget('active_event_edition');
        Cache::forget('global_app_settings');

        $edition = static::firstOrCreate(
            ['year' => $year],
            [
                'event_name' => 'TALENTA '.$year,
                'is_active' => true,
                'status' => 'open',
            ]
        );

        // Deactivate all others
        static::where('id', '!=', $edition->id)->update(['is_active' => false]);

        // Activate target edition
        $edition->update(['is_active' => true]);

        // Sync with app_settings
        AppSetting::set('event_year', $year);
        if (! empty($edition->event_name)) {
            AppSetting::set('event_name', $edition->event_name);
        }

        Cache::forget('active_event_edition');
        Cache::forget('global_app_settings');

        return $edition;
    }
}
