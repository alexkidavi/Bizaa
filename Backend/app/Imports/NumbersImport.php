<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NumbersImport implements ToCollection, WithHeadingRow
{
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
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
