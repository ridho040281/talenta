<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_group', // 'akademik', 'non_akademik'
        'slug',
        'icon',
        'description',
        'order',
    ];

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class)->orderBy('competitions.order', 'asc')->orderBy('competitions.id', 'asc');
    }

    public function getCategoryGroupLabelAttribute(): string
    {
        return $this->category_group === 'akademik' ? 'Akademik' : 'Non Akademik';
    }
}
