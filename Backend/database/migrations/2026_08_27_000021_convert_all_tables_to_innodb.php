<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Every table in this database was created on MyISAM, which has no
 * transaction or row-locking support at all — every DB::transaction() and
 * lockForUpdate() in the entire app (not just the stock/sale code) has been
 * silently a no-op. This converts every MyISAM table to InnoDB.
 *
 * MyISAM never enforced the FOREIGN KEY clauses declared in the original
 * CREATE TABLE statements (MySQL silently drops them for that engine), so
 * this ALTER only changes the storage engine — it does not retroactively
 * add FK constraints, and therefore isn't blocked by any referential
 * integrity drift that may have accumulated. Each table is converted
 * independently so one failure doesn't block the rest; failures are logged
 * and left on MyISAM for manual follow-up rather than aborting the batch.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tables = DB::select(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND ENGINE = 'MyISAM'"
        );

        $converted = [];
        $failed = [];

        foreach ($tables as $row) {
            $table = $row->TABLE_NAME;
            try {
                DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
                $converted[] = $table;
            } catch (\Throwable $e) {
                $failed[$table] = $e->getMessage();
                Log::warning("Failed to convert table {$table} to InnoDB: " . $e->getMessage());
            }
        }

        fwrite(STDOUT, "\n  Converted to InnoDB: " . count($converted) . " table(s)\n");
        if ($failed) {
            fwrite(STDOUT, "  FAILED to convert " . count($failed) . " table(s): " . implode(', ', array_keys($failed)) . "\n");
            fwrite(STDOUT, "  See laravel.log for details — these remain on MyISAM.\n");
        }
    }

    public function down(): void
    {
        // Deliberately irreversible — reverting to MyISAM would silently
        // reintroduce the exact bug this migration exists to fix.
    }
};
