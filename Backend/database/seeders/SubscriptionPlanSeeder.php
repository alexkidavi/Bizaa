<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'           => 'Free Trial',
                'slug'           => 'trial',
                'billing_period' => 'trial',
                'price'          => 0.00,
                'duration_days'  => 14,
                'features'       => [
                    'Full POS access',
                    'Inventory management',
                    'Sales & purchase tracking',
                    'Reports & analytics',
                    'Unlimited transactions',
                    'Up to 3 staff accounts',
                ],
                'is_active'  => true,
                'sort_order' => 0,
            ],
            [
                'name'           => 'Monthly Plan',
                'slug'           => 'monthly',
                'billing_period' => 'monthly',
                'price'          => 500.00,
                'duration_days'  => 30,
                'features'       => [
                    'Full POS access',
                    'Inventory management',
                    'Sales & purchase tracking',
                    'Reports & analytics',
                    'Unlimited transactions',
                    'Up to 5 staff accounts',
                    'Email support',
                ],
                'is_active'  => true,
                'sort_order' => 1,
            ],
            [
                'name'           => 'Quarterly Plan',
                'slug'           => 'quarterly',
                'billing_period' => 'quarterly',
                'price'          => 1400.00,
                'duration_days'  => 90,
                'features'       => [
                    'Full POS access',
                    'Inventory management',
                    'Sales & purchase tracking',
                    'Reports & analytics',
                    'Unlimited transactions',
                    'Up to 10 staff accounts',
                    'Priority support',
                    'Save KES 100 vs monthly',
                ],
                'is_active'  => true,
                'sort_order' => 2,
            ],
            [
                'name'           => 'Annual Plan',
                'slug'           => 'annually',
                'billing_period' => 'annually',
                'price'          => 5000.00,
                'duration_days'  => 365,
                'features'       => [
                    'Full POS access',
                    'Inventory management',
                    'Sales & purchase tracking',
                    'Reports & analytics',
                    'Unlimited transactions',
                    'Unlimited staff accounts',
                    'Priority support',
                    'Save KES 1,000 vs monthly',
                    'Custom branding (coming soon)',
                ],
                'is_active'  => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        $this->command->info('Subscription plans seeded.');
    }
}
