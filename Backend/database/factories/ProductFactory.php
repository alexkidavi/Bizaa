<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        // FMCG product catalog
        $products = [
            'Refined Sugar 1Kg',
            'Iodized Salt 1Kg',
            'Maize Flour 2Kg',
            'Rice Premium 2Kg',
            'Cooking Oil 1L',
            'Cooking Oil 2L',
            'Black Tea Leaves 500g',
            'Instant Coffee 100g',
            'Spaghetti Pasta 500g',
            'Macaroni Pasta 500g',
            'Milk Powder 500g',
            'Fresh Milk 500ml',
            'Baking Flour 2Kg',
            'Soap Bar 125g',
            'Laundry Detergent 1Kg',
            'Toothpaste 100ml',
            'Bottled Water 1L',
            'Soft Drink 500ml',
            'Biscuits Assorted 200g',
            'Tissue Paper 4 Pack',
        ];

        $name = $this->faker->randomElement($products);

        // Base cost price (realistic FMCG range)
        $buyingPrice = $this->faker->randomFloat(2, 10, 500);

        // Selling price
        $sellingPrice = $this->faker->randomFloat(
            2,
            $buyingPrice + 5,
            $buyingPrice + 200
        );

        // Wholesale price
        $wholesalePrice = $this->faker->randomFloat(
            2,
            $buyingPrice + 2,
            $sellingPrice - 2
        );

        $openingStock = rand(20, 1000);
        $soldStock = rand(0, $openingStock);

        return [
            'name' => $name,
            'product_code' => strtoupper($this->faker->unique()->bothify('PRO-#####')),
            'barcode' => $this->faker->ean13(),
            // Picks an existing row rather than hardcoding id 1 (which
            // assumed a specific seed order and broke as soon as it
            // didn't hold), but doesn't force-create a new Business/User
            // via the factory relationship either — ProductSeeder calls
            // ->make() and immediately overwrites these with its own
            // randomly-selected existing IDs, so an eager factory()
            // relationship here would silently create orphaned rows on
            // every seeder run for no reason.
            'business_id' => \App\Models\Business::inRandomOrder()->value('id') ?? \App\Models\Business::factory(),
            'user_id' => \App\Models\User::inRandomOrder()->value('id') ?? \App\Models\User::factory(),

            // 'category_id' => rand(1, 10),
            // 'brand_id' => rand(1, 10),
            // 'unit_id' => rand(1, 6),

            'buying_price' => $buyingPrice,
            'selling_price' => $sellingPrice,
            'wholesale_price' => $wholesalePrice,

            'tax_type' => $this->faker->randomElement(['vat', 'non_vat']),

            'opening_stock' => $openingStock,
            'reorder_level' => rand(10, 100),

            'current_stock' => max(0, $openingStock - $soldStock),

            'for_sale' => 1,
            'for_purchase' => 1,

            'color' => null,
            'image' => null,

            'parent_id' => null,
        ];
    }
}