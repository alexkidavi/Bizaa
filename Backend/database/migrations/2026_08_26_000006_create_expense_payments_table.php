<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method');
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->date('payment_date');
            $table->timestamps();
            $table->index('expense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_payments');
    }
};
