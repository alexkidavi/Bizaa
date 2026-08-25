<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Models\Branch;
use App\Models\User;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::create([
            'business_name'     => 'Demo Business',
            'business_industry' => 'Technology',
            'business_email'    => 'demo@business.com',
            'business_phone'    => '0712345678',
            'business_website'  => 'https://demo-business.com',
            'business_location' => 'Nairobi, Kenya',
            'business_logo'     => null,
            'business_code'     => 'DEMO001',
        ]);

       
    }
}