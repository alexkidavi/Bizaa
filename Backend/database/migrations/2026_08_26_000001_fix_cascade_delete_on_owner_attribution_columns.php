<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * products.user_id, purchases.user_id, invoices.user_id,
 * purchase_payments.user_id, invoice_payments.user_id, and
 * quotations.user_id record who created the row, not what the row
 * structurally belongs to (that's business_id) — but were created with
 * cascadeOnDelete(), so removing a staff account silently wiped out every
 * product, purchase, invoice, quotation, and payment they had ever
 * created, including KRA-reported financial/tax records.
 *
 * The later HRM-era migrations (expenses, departments, attendance,
 * overtime, leaves, payroll) already use nullOnDelete() for the
 * equivalent created_by/recorded_by/approved_by columns — this migration
 * brings the earlier core-module tables in line with that same pattern.
 *
 * Editing the original CREATE TABLE migrations (also done, for a clean
 * install) has no effect on a database where they already ran — this
 * migration is what actually fixes an existing environment's schema.
 */
return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    protected array $targets = [
        'products'          => 'user_id',
        'purchases'         => 'user_id',
        'invoices'          => 'user_id',
        'purchase_payments' => 'user_id',
        'invoice_payments'  => 'user_id',
        'quotations'        => 'user_id',
    ];

    public function up(): void
    {
        foreach ($this->targets as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $foreignKey = "{$table}_{$column}_foreign";

            Schema::table($table, function ($blueprint) use ($foreignKey) {
                $blueprint->dropForeign($foreignKey);
            });

            // Raw MODIFY rather than Blueprint::change() — that needs
            // doctrine/dbal, which isn't installed in this project.
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NULL");

            Schema::table($table, function ($blueprint) use ($column, $foreignKey) {
                $blueprint->foreign($column, $foreignKey)
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->targets as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $foreignKey = "{$table}_{$column}_foreign";

            Schema::table($table, function ($blueprint) use ($foreignKey) {
                $blueprint->dropForeign($foreignKey);
            });

            // Not reverted to NOT NULL: any row with user_id already set to
            // NULL by this migration's up() would break that constraint.
            // down() only restores the original cascade-delete behavior.
            Schema::table($table, function ($blueprint) use ($column, $foreignKey) {
                $blueprint->foreign($column, $foreignKey)
                    ->references('id')->on('users')
                    ->cascadeOnDelete();
            });
        }
    }
};
