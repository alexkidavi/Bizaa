<?php
// =====================================================================
// 2024_01_01_000012_create_invoice_payments_table.php
// =====================================================================
//
// NOTE: this file originally also embedded copies of the quotations/
// quotation_items table definitions after this return statement. PHP
// only executes the first top-level `return` in an included file, so
// those blocks were dead code that never actually ran — quotations and
// quotation_items are created by their own dedicated migration files
// (2026_05_01_112940_create_quotations_table.php and
// 2026_05_01_203616_create_quotation_items_table.php), which is what
// was actually happening at migrate time. Removed here for clarity.
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            // nullable: who recorded the payment, not what it belongs to.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method');
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->date('payment_date');
            $table->timestamps();
            $table->index('invoice_id');
        });
    }
    public function down(): void { Schema::dropIfExists('invoice_payments'); }
};
