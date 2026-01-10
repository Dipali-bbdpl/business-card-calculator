<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Option;
use App\Models\Pricing;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index($slug = null)
{
    $category = Category::where('slug', $slug ?? 'business-cards')
        ->with(['subcategories.options', 'pricings'])
        ->firstOrFail();

    // Get default options
    $defaults = [];
    foreach ($category->subcategories as $subcategory) {
        // First, try to get option marked as default
        $defaultOption = $subcategory->options->where('is_default', true)->first();
        
        if ($defaultOption) {
            $defaults[$subcategory->type] = $defaultOption->id;
        } else {
            // If no default, get the first option
            $firstOption = $subcategory->options->first();
            if ($firstOption) {
                $defaults[$subcategory->type] = $firstOption->id;
            }
        }
    }

    // Debug: Check what defaults are set
    // Log::info('Calculator defaults:', $defaults);

    // Calculate initial pricing
    $initialPricing = $this->calculatePricing($category->id, $defaults);
    

    return view('calculator.index', compact('category', 'defaults', 'initialPricing'));
}


    public function calculate(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'size' => 'required|exists:options,id',
            'finish' => 'required|exists:options,id',
            'corners' => 'required|exists:options,id',
        ]);

        $pricing = $this->calculatePricing($request->category_id, [
            'size' => $request->size,
            'finish' => $request->finish,
            'corners' => $request->corners,
        ]);

        return response()->json($pricing);
    }

    private function calculatePricing($categoryId, $selectedOptions)
    {
        $category = Category::findOrFail($categoryId);
        
        // Get selected options
        $size = Option::find($selectedOptions['size']);
        $finish = Option::find($selectedOptions['finish']);
        $corners = Option::find($selectedOptions['corners']);
        
        // Calculate adjustment per card
        $adjustmentPerCard = 0;
        if ($size) $adjustmentPerCard += $size->price_adjustment;
        if ($finish) $adjustmentPerCard += $finish->price_adjustment;
        if ($corners) $adjustmentPerCard += $corners->price_adjustment;
        
        // Get base pricing
        $basePricings = Pricing::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('quantity')
            ->get();

        // Calculate final pricing
        $pricingTable = [];
        foreach ($basePricings as $pricing) {
            $finalPricePerCard = $pricing->price_per_unit + $adjustmentPerCard;
            $finalPackPrice = round($finalPricePerCard * $pricing->quantity, 2);
            
            // Calculate original price with adjustment
            $originalPrice = $pricing->original_price;
            if ($originalPrice) {
                $basePricePerCard = $pricing->pack_price / $pricing->quantity;
                $originalPrice = round(($basePricePerCard + $adjustmentPerCard) * $pricing->quantity * 1.189, 2);
            }
            
            $savings = $originalPrice ? $originalPrice - $finalPackPrice : 0;
            $savingsPercentage = $originalPrice ? round(($savings / $originalPrice) * 100) : 0;

            $pricingTable[] = [
                'quantity' => $pricing->quantity,
                'price_per_card' => $finalPricePerCard,
                'pack_price' => $finalPackPrice,
                'original_price' => $originalPrice,
                'is_recommended' => $pricing->is_recommended,
                'badge' => $pricing->badge,
                'savings' => $savings,
                'savings_percentage' => $savingsPercentage,
                'formatted' => [
                    'price_per_card' => '$' . number_format($finalPricePerCard, 2),
                    'pack_price' => '$' . number_format($finalPackPrice, 2),
                    'original_price' => $originalPrice ? '$' . number_format($originalPrice, 2) : null,
                ]
            ];
        }

        return [
            'success' => true,
            'selected_options' => [
                'size' => $size,
                'finish' => $finish,
                'corners' => $corners,
            ],
            'adjustment_per_card' => $adjustmentPerCard,
            'pricing_table' => $pricingTable,
        ];
    }
}