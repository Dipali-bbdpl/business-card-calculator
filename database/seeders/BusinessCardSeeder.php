<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Option;
use App\Models\Pricing;
use Illuminate\Support\Facades\DB;

class BusinessCardSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        Category::truncate();
        Subcategory::truncate();
        Option::truncate();
        Pricing::truncate();

        // Create Business Cards Category
        $businessCards = Category::create([
            'name' => 'Business Cards',
            'slug' => 'business-cards',
            'description' => 'Professional business cards with various customization options',
            'is_active' => true,
        ]);

        // Create Size Subcategory
        $sizeSubcategory = Subcategory::create([
            'category_id' => $businessCards->id,
            'name' => 'Size',
            'type' => 'size',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Size Options with price adjustments
        Option::create([
            'subcategory_id' => $sizeSubcategory->id,
            'name' => 'Standard',
            'description' => '2.0" x 3.5"',
            'price_adjustment' => 0.00,
            'is_default' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Option::create([
            'subcategory_id' => $sizeSubcategory->id,
            'name' => 'MOO',
            'description' => '2.16" x 3.3"',
            'price_adjustment' => 0.02,  // +$0.02 per card
            'is_default' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Option::create([
            'subcategory_id' => $sizeSubcategory->id,
            'name' => 'Square',
            'description' => '2.56" x 2.56"',
            'price_adjustment' => 0.03,  // +$0.03 per card
            'is_default' => false,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Create Finish Subcategory
        $finishSubcategory = Subcategory::create([
            'category_id' => $businessCards->id,
            'name' => 'Finish',
            'type' => 'finish',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Finish Options with price adjustments
        Option::create([
            'subcategory_id' => $finishSubcategory->id,
            'name' => 'Matte',
            'description' => 'With a smooth feel. Shine-free so no glare.',
            'price_adjustment' => 0.00,
            'is_default' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Option::create([
            'subcategory_id' => $finishSubcategory->id,
            'name' => 'Gloss',
            'description' => 'Eye-catchingly shiny. Makes color photos pop.',
            'price_adjustment' => 0.01,  // +$0.01 per card
            'is_default' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Create Corners Subcategory
        $cornerSubcategory = Subcategory::create([
            'category_id' => $businessCards->id,
            'name' => 'Corners',
            'type' => 'corners',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Corner Options with price adjustments
        Option::create([
            'subcategory_id' => $cornerSubcategory->id,
            'name' => 'Square',
            'description' => 'Sharp and Stylish',
            'price_adjustment' => 0.00,
            'is_default' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Option::create([
            'subcategory_id' => $cornerSubcategory->id,
            'name' => 'Rounded',
            'description' => 'Smooth & Rounded',
            'price_adjustment' => 0.02,  // +$0.02 per card
            'is_default' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Create Pricing with your exact base prices
        $pricings = [
            ['quantity' => 50, 'price_per_unit' => 0.44, 'pack_price' => 22.00, 'original_price' => null],
            ['quantity' => 100, 'price_per_unit' => 0.43, 'pack_price' => 43.00, 'original_price' => 44.00],
            ['quantity' => 200, 'price_per_unit' => 0.37, 'pack_price' => 74.00, 'original_price' => 88.00],
            ['quantity' => 400, 'price_per_unit' => 0.32, 'pack_price' => 129.00, 'original_price' => 176.00],
            ['quantity' => 600, 'price_per_unit' => 0.27, 'pack_price' => 163.00, 'original_price' => 264.00, 'is_recommended' => true, 'badge' => 'RECOMMENDED'],
            ['quantity' => 800, 'price_per_unit' => 0.27, 'pack_price' => 217.00, 'original_price' => 352.00],
            ['quantity' => 1000, 'price_per_unit' => 0.25, 'pack_price' => 250.00, 'original_price' => 440.00],
        ];

        foreach ($pricings as $pricing) {
            Pricing::create([
                'category_id' => $businessCards->id,
                'quantity' => $pricing['quantity'],
                'price_per_unit' => $pricing['price_per_unit'],
                'pack_price' => $pricing['pack_price'],
                'original_price' => $pricing['original_price'] ?? null,
                'is_recommended' => $pricing['is_recommended'] ?? false,
                'badge' => $pricing['badge'] ?? null,
                'is_active' => true,
                'sort_order' => $pricing['quantity'],
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Business Card pricing data seeded successfully!');
    }
}