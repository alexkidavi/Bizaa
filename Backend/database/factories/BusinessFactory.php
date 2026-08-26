<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_name'     => fake()->unique()->company(),
            'business_industry' => fake()->randomElement(['Retail', 'Wholesale', 'Restaurant', 'Pharmacy', 'Electronics']),
            'business_email'    => fake()->unique()->companyEmail(),
            'business_phone'    => '2547' . fake()->unique()->numerify('########'),
            'business_location' => fake()->city(),
            'business_code'     => strtoupper(fake()->unique()->bothify('BIZ-####')),
        ];
    }
}
