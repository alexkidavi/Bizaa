<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * KRA eTIMS item classification codes (itemClsCd / itemClsNm / itemClsLvl),
 * loaded from the official table pasted by the client into
 * database/seeders/data/digitax_item_classes.tsv (tab-separated, header row
 * first). Used to power the searchable classification picker on the
 * product form — this table is far too large (1000+ rows, growing) for a
 * plain dropdown.
 */
class DigitaxItemClassSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/digitax_item_classes.tsv');

        if (! file_exists($path)) {
            $this->command?->warn("Skipping DigitaxItemClassSeeder: {$path} not found.");
            return;
        }

        $handle = fopen($path, 'r');
        fgetcsv($handle, 0, "\t"); // skip header row

        $now = now();
        $batch = [];

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (count($row) < 3 || $row[0] === '') {
                continue;
            }

            $batch[] = [
                'code' => trim($row[0]),
                'name' => trim($row[1]),
                'level' => (int) $row[2],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 200) {
                DB::table('digitax_item_classes')->upsert($batch, ['code'], ['name', 'level', 'updated_at']);
                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('digitax_item_classes')->upsert($batch, ['code'], ['name', 'level', 'updated_at']);
        }

        fclose($handle);
    }
}
