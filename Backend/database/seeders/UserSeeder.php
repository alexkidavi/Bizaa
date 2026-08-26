<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::firstOrFail();

        // The demo admin account is only ever needed for local development
        // and automated tests; it must never be created against a real
        // (staging/production) database via `db:seed`/`migrate --seed`.
        if (app()->environment('local', 'testing')) {
            $password = 'Demo@2026';
        } else {
            $password = Str::random(32);
        }

        $user = User::create([
            'name'          => 'Test Admin',
            'business_id'   => $business->id,
            'email'         => 'admin@brimsoftsolutions.com',
            'phone'         => '0700000000',
            'role_id'       => 1,
            'status'        => 'active',
            'registered_by' => '',
            'password'      => Hash::make($password),
        ]);

        if (!app()->environment('local', 'testing')) {
            $this->command?->warn("UserSeeder: generated one-time password for admin@brimsoftsolutions.com: {$password}");
        }

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