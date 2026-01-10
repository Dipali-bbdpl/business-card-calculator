<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pricing extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'quantity', 'price_per_unit', 'pack_price', 'original_price', 'is_recommended', 'badge', 'sort_order', 'is_active'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}