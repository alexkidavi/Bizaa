<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NumbersImport implements ToCollection, WithHeadingRow
{
    /**
     * Every row here becomes a WhatsApp send target — an unbounded upload
     * is effectively an unbounded send list. Same cap as ProductsImport.
     */
    protected const MAX_ROWS = 1000;

    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows->take(self::MAX_ROWS) as $row) {
            // Ensure the columns exist
            if (! isset($row['phone'], $row['message'])) {
                continue;
            }
            // Skip invalid phone numbers
            if (empty($row['phone'])) {
                continue;
            }

            $this->data[] = [
                // 'name' => $row['name'],
                'phone'   => $row['phone'],
                'message' => $row['message'],
            ];
        }
    }
}
