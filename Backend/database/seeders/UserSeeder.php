<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::firstOrFail();

        $user = User::create([
            'name'          => 'Test Admin',
            'business_id'   => $business->id,
            'email'         => 'admin@brimsoftsolutions.com',
            'phone'         => '0700000000',
            'role_id'       => 1,
            'status'        => 'active',
            'registered_by' => '',
            'password'      => Hash::make('Demo@2026'),
        ]);

        // Branches
        $branches = [
            'Kasarani Branch',
            'Lavington Branch',
            'Kenyatta Road Branch',
        ];

        foreach ($branches as $branch) {
            Branch::create([
                'name' => $branch,
                'user_id' => $user->id,
                'business_id' => $business->id,
            ]);
        }

        // Units
        $units = [
            ['name' => 'Piece', 'abbreviation' => 'pc'],
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Gram', 'abbreviation' => 'g'],
            ['name' => 'Liter', 'abbreviation' => 'L'],
            ['name' => 'Milliliter', 'abbreviation' => 'ml'],
            ['name' => 'Pack', 'abbreviation' => 'pk'],
        ];

        foreach ($units as $unit) {
            Unit::create([
                'name' => $unit['name'],
                'abbreviation' => $unit['abbreviation'],
                'user_id' => $user->id,
                'business_id' => $business->id,
            ]);
        }
    }
}