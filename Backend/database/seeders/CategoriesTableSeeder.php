<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\Business;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Clothing',
            'Footwear',
            'Accessories',
            'Electronics',
            'Home & Living',
            'Beauty & Personal Care',
            'Groceries',
            'Baby Products',
            'Toys & Games',
            'Sports & Outdoors',
        ];

        $user = User::first();
        $business = Business::first();

        foreach ($categories as $category) {
            Category::firstOrCreate(
                [
                    'name' => $category,
                    'business_id' => $business->id,
                ],
                [
                    'description' => $category,
                    'user_id' => $user->id,
                ]
            );
        }
    }
}