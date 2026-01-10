<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Option;
use App\Models\Pricing;

class PricingCalculator
{
    public function calculate($categoryId, $sizeId, $finishId, $cornersId, $quantity)
    {
        $category = Category::findOrFail($categoryId);
        
        // Get base pricing for quantity
        $pricing = Pricing::where('category_id', $categoryId)
            ->where('quantity', '<=', $quantity)
            ->orderBy('quantity', 'desc')
            ->first();

        if (!$pricing) {
            $pricing = Pricing::where('category_id', $categoryId)
                ->orderBy('quantity', 'desc')
                ->first();
        }

        // Get option adjustments
        $options = Option::whereIn('id', [$sizeId, $finishId, $cornersId])->get();
        
        $basePrice = $pricing->price_per_card;
        $adjustment = 0;

        foreach ($options as $option) {
            $adjustment += $option->price_adjustment;
        }

        $pricePerCard = $basePrice + $adjustment;
        $totalPrice = $pricePerCard * $quantity;

        return [
            'success' => true,
            'quantity' => $quantity,
            'price_per_card' => round($pricePerCard, 2),
            'total_price' => round($totalPrice, 2),
            'base_price' => $basePrice,
            'adjustment' => $adjustment,
            'pricing_tier' => $pricing,
            'options' => $options->pluck('name', 'id'),
            'currency' => config('app.currency', 'USD'),
            'formatted' => [
                'price_per_card' => '$' . number_format($pricePerCard, 2),
                'total_price' => '$' . number_format($totalPrice, 2),
                'adjustment' => '$' . number_format($adjustment, 2),
            ]
        ];
    }

    public function calculateWithOptions($categoryId, $options, $quantity)
    {
        $category = Category::findOrFail($categoryId);
        
        $pricing = Pricing::where('category_id', $categoryId)
            ->where('quantity', '<=', $quantity)
            ->orderBy('quantity', 'desc')
            ->first();

        if (!$pricing) {
            $pricing = Pricing::where('category_id', $categoryId)
                ->orderBy('quantity', 'desc')
                ->first();
        }

        $basePrice = $pricing->price_per_card;
        $adjustment = 0;

        foreach ($options as $optionId) {
            $option = Option::find($optionId);
            if ($option) {
                $adjustment += $option->price_adjustment;
            }
        }

        $pricePerCard = $basePrice + $adjustment;
        $totalPrice = $pricePerCard * $quantity;

        return [
            'success' => true,
            'quantity' => $quantity,
            'price_per_card' => round($pricePerCard, 2),
            'total_price' => round($totalPrice, 2),
            'base_price' => $basePrice,
            'adjustment' => $adjustment,
            'currency' => config('app.currency', 'USD'),
        ];
    }

    public function getRecommendedQuantity($categoryId)
    {
        $pricing = Pricing::where('category_id', $categoryId)
            ->where('is_recommended', true)
            ->first();

        return $pricing ? $pricing->quantity : 100;
    }

    public function getPricingTable($categoryId)
    {
        $pricings = Pricing::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('quantity')
            ->get();

        return $pricings->map(function ($pricing) {
            return [
                'quantity' => $pricing->quantity,
                'price_per_card' => $pricing->price_per_card,
                'pack_price' => $pricing->pack_price,
                'original_price' => $pricing->original_price,
                'is_recommended' => $pricing->is_recommended,
                'badge' => $pricing->badge,
                'savings' => $pricing->savings,
                'savings_percentage' => $pricing->savings_percentage,
                'formatted' => [
                    'price_per_card' => '$' . number_format($pricing->price_per_card, 2),
                    'pack_price' => '$' . number_format($pricing->pack_price, 2),
                    'original_price' => $pricing->original_price ? '$' . number_format($pricing->original_price, 2) : null,
                ]
            ];
        });
    }
}