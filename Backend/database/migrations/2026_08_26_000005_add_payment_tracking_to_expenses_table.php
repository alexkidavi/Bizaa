<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('amount_paid', 15, 2)->default(0)->after('amount');
            $table->decimal('balance', 15, 2)->default(0)->after('amount_paid');
            $table->enum('payment_status', ['paid', 'partially_paid', 'pending'])->default('pending')->after('balance');
        });

        // Existing expenses predate payment tracking — treat them as
        // already settled rather than surfacing them all as "pending".
        DB::table('expenses')->update([
            'amount_paid'    => DB::raw('amount'),
            'balance'        => 0,
            'payment_status' => 'paid',
        ]);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'balance', 'payment_status']);
        });
    }
};
