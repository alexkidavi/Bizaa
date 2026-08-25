<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // KRA eTIMS classification — required by DigiTax to register an item.
            // Nullable: not every product is synced (e.g. for_sale=false), and
            // existing products predate this integration.
            $table->string('item_class_code')->nullable()->after('tax_type');
            $table->string('item_type_code')->nullable()->default('2')->after('item_class_code'); // 1=Raw Material, 2=Finished Product, 3=Service
            $table->string('tax_type_code')->nullable()->after('item_type_code'); // A/B/C/D/E — overrides the vat/non_vat -> code default mapping
            $table->string('package_unit_code')->nullable()->after('tax_type_code');
            $table->string('quantity_unit_code')->nullable()->after('package_unit_code');
            $table->string('origin_nation_code')->default('KE')->after('quantity_unit_code');

            // Sync tracking against DigiTax
            $table->string('digitax_item_id')->nullable()->after('origin_nation_code');
            $table->string('etims_item_code')->nullable()->after('digitax_item_id');
            $table->string('digitax_status')->nullable()->after('etims_item_code'); // PENDING, COMPLETE, FAILED
            $table->timestamp('digitax_synced_at')->nullable()->after('digitax_status');
            $table->text('digitax_error')->nullable()->after('digitax_synced_at');

            $table->index('digitax_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'item_class_code',
                'item_type_code',
                'tax_type_code',
                'package_unit_code',
                'quantity_unit_code',
                'origin_nation_code',
                'digitax_item_id',
                'etims_item_code',
                'digitax_status',
                'digitax_synced_at',
                'digitax_error',
            ]);
        });
    }
};
