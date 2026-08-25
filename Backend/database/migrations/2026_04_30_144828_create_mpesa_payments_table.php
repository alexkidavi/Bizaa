<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mpesa_payments', function (Blueprint $table) {
            $table->id();

            $table->decimal('amount', 10, 2);
            $table->string('mpesa_receipt_number')->unique();

            $table->string('phonenumber')->nullable();
            $table->timestamp('transaction_date')->nullable();

            $table->string('checkout_request_id')->index();

            // Optional links (POS / orders)
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_payments');
    }
};