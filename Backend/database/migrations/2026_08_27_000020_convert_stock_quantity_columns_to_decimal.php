<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Every quantity/stock column in the inventory path was an integer,
 * silently truncating fractional quantities (e.g. 10.2 kg) on save.
 * Brings them in line with the decimal(15,4) precision already used by
 * purchase_items/invoice_items/quotation_items.quantity. Raw ALTER
 * statements are used because doctrine/dbal (required by Schema::change())
 * isn't installed, and MySQL's enum type needs a raw MODIFY regardless.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY COLUMN opening_stock DECIMAL(15,4) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE products MODIFY COLUMN current_stock DECIMAL(15,4) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE product_branch MODIFY COLUMN stock DECIMAL(15,4) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE stock_take_items MODIFY COLUMN recorded_stock DECIMAL(15,4) NOT NULL');
        DB::statement('ALTER TABLE stock_take_items MODIFY COLUMN counted_stock DECIMAL(15,4) NULL');
        DB::statement('ALTER TABLE sale_items MODIFY COLUMN quantity DECIMAL(15,4) NOT NULL');

        // 'sale' is a new history type — sales previously left no
        // stock_histories audit trail at all.
        DB::statement("ALTER TABLE stock_histories MODIFY COLUMN type ENUM('purchase','adjustment','transfer','sale') NOT NULL");
        DB::statement('ALTER TABLE stock_histories MODIFY COLUMN quantity DECIMAL(15,4) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE stock_histories MODIFY COLUMN quantity INT NOT NULL');
        DB::statement("ALTER TABLE stock_histories MODIFY COLUMN type ENUM('purchase','adjustment','transfer') NOT NULL");
        DB::statement('ALTER TABLE sale_items MODIFY COLUMN quantity INT NOT NULL');
        DB::statement('ALTER TABLE stock_take_items MODIFY COLUMN counted_stock INT NULL');
        DB::statement('ALTER TABLE stock_take_items MODIFY COLUMN recorded_stock INT NOT NULL');
        DB::statement('ALTER TABLE product_branch MODIFY COLUMN stock INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE products MODIFY COLUMN current_stock INT NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE products MODIFY COLUMN opening_stock INT NOT NULL DEFAULT 0');
    }
};
