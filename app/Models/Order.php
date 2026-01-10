<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'category_id', 'selected_options',
        'quantity', 'price_per_card', 'total_price', 'status',
        'customer_name', 'customer_email', 'customer_phone',
        'customer_address', 'customer_city', 'customer_state',
        'customer_zip_code', 'customer_country', 'payment_method',
        'notes', 'tracking_number', 'shipped_at'
    ];

    protected $casts = [
        'selected_options' => 'array',
        'shipped_at' => 'datetime',
        'total_price' => 'decimal:2',
        'price_per_card' => 'decimal:2',
    ];

    protected $dates = [
        'shipped_at'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'production' => 'bg-purple-100 text-purple-800',
            'shipped' => 'bg-green-100 text-green-800',
            'delivered' => 'bg-green-500 text-white',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->total_price, 2);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            $order->order_number = 'ORD-' . strtoupper(uniqid());
            
            if (auth()->check()) {
                $order->user_id = auth()->id();
            }
        });
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['shipped', 'delivered']);
    }

    public function getEstimatedDeliveryDateAttribute()
    {
        if ($this->shipped_at) {
            return $this->shipped_at->addDays(5)->format('M d, Y');
        }
        
        return now()->addDays(8)->format('M d, Y');
    }
}