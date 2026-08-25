<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->nullable()->default(16.00);
            $table->string('kra_pin')->nullable();
            $table->text('tax_api_key')->nullable();

            $table->string('mpesa_shortcode')->nullable();
            $table->string('mpesa_shortcode_type')->nullable()->default('paybill');
            $table->text('mpesa_passkey')->nullable();
            $table->text('mpesa_consumer_key')->nullable();
            $table->text('mpesa_consumer_secret')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'tax_rate',
                'kra_pin',
                'tax_api_key',
                'mpesa_shortcode',
                'mpesa_shortcode_type',
                'mpesa_passkey',
                'mpesa_consumer_key',
                'mpesa_consumer_secret',
            ]);
        });
    }
};
