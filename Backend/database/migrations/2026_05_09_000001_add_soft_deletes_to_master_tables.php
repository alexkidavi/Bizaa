<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add soft-delete support to all master-data tables.
 *
 * Transaction tables (purchases, invoices, quotations) already have
 * deleted_at via their own migrations — only master / lookup tables are
 * targeted here. Running this migration is the only DB change required.
 */
return new class extends Migration
{
    /** Tables that need a deleted_at column, in dependency order. */
    private array $tables = [
        'products',
        'customers',
        'suppliers',
        'categories',
        'brands',
        'units',
        'branches',
        'sales',
        'expenses',
        'expense_categories',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
