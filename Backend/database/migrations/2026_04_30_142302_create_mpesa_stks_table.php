<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('mpesa_stks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('business_id')->constrained()->onDelete('cascade');
        $table->string('merchant_request_id')->nullable();
        $table->string('checkout_request_id')->nullable();

        $table->string('phone_number');
        $table->integer('amount');

        $table->string('reference')->nullable();
        $table->string('transaction_desc')->nullable();

        $table->string('response_code')->nullable();
        $table->text('response_description')->nullable();
        $table->text('customer_message')->nullable();

        $table->tinyInteger('status')->default(0);

        $table->json('raw_response')->nullable(); 

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_stks');
    }
};
