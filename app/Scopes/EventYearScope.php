<?php

namespace App\Scopes;

use App\Models\AppSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class EventYearScope implements Scope
{
    protected static array $verifiedTables = [];

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        if (! isset(static::$verifiedTables[$table])) {
            try {
                if (Schema::hasColumn($table, 'event_year')) {
                    static::$verifiedTables[$table] = true;
                }
            } catch (\Throwable $e) {
                // Table might not exist yet
            }
        }

        if (isset(static::$verifiedTables[$table])) {
            $year = AppSetting::getActiveYear();
            if (! empty($year)) {
                $builder->where($table.'.event_year', $year);
            }
        }
    }
}
