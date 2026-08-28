<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Loyalty ships enabled — a business has to deliberately turn it off
     * under Settings > Loyalty, not opt in. Raw SQL rather than
     * Blueprint::change() to avoid a doctrine/dbal dependency this app
     * doesn't otherwise need.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE loyalty_settings MODIFY is_enabled TINYINT(1) NOT NULL DEFAULT 1');

        // This is a brand-new feature — no business has had the chance to
        // deliberately opt out yet, so every existing row still sitting at
        // the old false default is just unconfigured, not a real choice.
        // Safe to flip once, now; from here on a business turning it off
        // is a real decision this migration will never touch again.
        DB::table('loyalty_settings')->where('is_enabled', false)->update(['is_enabled' => true]);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE loyalty_settings MODIFY is_enabled TINYINT(1) NOT NULL DEFAULT 0');
    }
};
