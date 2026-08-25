<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $industries = [
            'Technology',
            'Healthcare',
            'Education',
            'Finance & Banking',
            'Real Estate',
            'Manufacturing',
            'Retail & E-commerce',
            'Transportation & Logistics',
            'Hospitality & Tourism',
            'Construction',
            'Agriculture',
            'Energy & Utilities',
            'Media & Entertainment',
            'Telecommunications',
            'Government & Public Sector',
            'NGO / Nonprofit',
            'Legal Services',
            'Security Services',
            'Automotive',
            'Marketing & Advertising'
        ];

        foreach ($industries as $industry) {
            Industry::firstOrCreate(['name' => $industry]);
        }
    }
}
