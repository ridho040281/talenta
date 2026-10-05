<?php

namespace App\Scopes;

use App\Models\AppSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EventYearScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $year = AppSetting::getActiveYear();
        if (! empty($year)) {
            $builder->where($model->getTable().'.event_year', $year);
        }
    }
}
