<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Business;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $total = 50;
        $batchSize = 10;

        // Fetch IDs once (more efficient)
        $categoryIds = Category::pluck('id');
        $businessIds = Business::pluck('id');
        $userIds = User::pluck('id');
        $branchIds = Branch::pluck('id');

        for ($i = 0; $i < $total; $i += $batchSize) {

            $products = Product::factory()
                ->count($batchSize)
                ->make();

            foreach ($products as $product) {

                // Assign random related IDs
                $product->category_id = $categoryIds->random();
                $product->business_id = $businessIds->random();
                $product->user_id = $userIds->random();

                $product->save();

                // Attach random branches (1–3)
               $selectedBranches = $branchIds
    ->random(rand(1, min(3, $branchIds->count())))
    ->values()
    ->all();

            foreach ($selectedBranches as $branchId) {
    DB::table('product_branch')->insert([
        'product_id' => $product->id,
        'branch_id' => $branchId,
        'stock' => rand(0, 200),
        'buying_price' => $product->buying_price,
        'selling_price' => $product->selling_price,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
            }
        }
    }
}