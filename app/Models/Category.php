<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(Pricing::class)->where('is_active', true)->orderBy('quantity');
    }
}