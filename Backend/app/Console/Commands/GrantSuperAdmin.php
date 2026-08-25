<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class GrantSuperAdmin extends Command
{
    protected $signature   = 'superadmin:grant {email}';
    protected $description = 'Grant super admin access to a user by email';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email: {$email}");
            return 1;
        }

        if ($user->is_superadmin) {
            $this->warn("{$email} is already a super admin.");
            return 0;
        }

        if (!$this->confirm("Grant super admin to {$user->name} ({$email})?")) {
            $this->info('Cancelled.');
            return 0;
        }

        $role = Role::where('name', 'superadmin')->whereNull('business_id')->first();

        $user->update([
            'is_superadmin' => true,
            'business_id'   => null,
            'role_id'       => $role?->id,
        ]);

        $this->info("✓ Super admin granted to {$user->name} ({$email}).");
        $this->warn('This user no longer belongs to any business and bypasses all permission checks.');

        return 0;
    }
}
