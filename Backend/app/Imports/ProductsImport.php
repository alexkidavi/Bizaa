<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Repositories\ProductRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

/**
 * Bulk-creates products from an uploaded spreadsheet, reusing
 * ProductRepository::create() so imported rows go through the exact
 * same creation path (branch stock attach, defaults) as the manual
 * "Add Product" form. Never throws on a bad row — every row either
 * imports, is skipped as a duplicate, or is recorded as an error, so
 * one bad row never aborts the rest of the file.
 */
class ProductsImport implements ToCollection, WithHeadingRow
{
    /**
     * Runs synchronously in the request (no queue worker is set up in this
     * environment yet) — cap the sheet size so a single upload can't run
     * long enough to time out or tie up a request indefinitely.
     */
    protected const MAX_ROWS = 1000;

    protected array $categoryIdsByName = [];
    protected array $unitIdsByName = [];

    protected int $imported = 0;
    protected int $skipped = 0;
    protected array $errors = [];

    public function __construct(
        protected ProductRepository $repo,
        protected int $businessId,
        protected int $userId,
        protected int $branchId
    ) {
        $this->categoryIdsByName = Category::where('business_id', $businessId)
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [Str::lower(trim($name)) => $id])
            ->all();

        $this->unitIdsByName = Unit::where('business_id', $businessId)
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [Str::lower(trim($name)) => $id])
            ->all();
    }

    public function collection(Collection $rows)
    {
        if ($rows->count() > self::MAX_ROWS) {
            $this->errors[] = [
                'row' => 0,
                'message' => 'This file has ' . number_format($rows->count()) . ' rows, which is over the '
                    . number_format(self::MAX_ROWS) . '-row limit per import. Please split it into smaller files.',
            ];

            return;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // account for the header row

            $name = trim((string) ($row['product_name'] ?? ''));
            $categoryName = trim((string) ($row['category'] ?? ''));
            $unitName = trim((string) ($row['unit'] ?? ''));
            $buyingPrice = $row['buying_price'] ?? null;
            $sellingPrice = $row['selling_price'] ?? null;
            $openingStock = $row['opening_stock'] ?? null;

            // Skip fully blank rows silently (trailing empty rows are common in spreadsheets)
            if ($name === '' && $categoryName === '' && $unitName === '') {
                continue;
            }

            if ($name === '') {
                $this->errors[] = ['row' => $rowNumber, 'message' => 'Product Name is required.'];
                continue;
            }

            if (!is_numeric($buyingPrice) || !is_numeric($sellingPrice) || !is_numeric($openingStock)) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'message' => 'Buying Price, Selling Price and Opening Stock must all be numbers.',
                ];
                continue;
            }

            $categoryId = $this->categoryIdsByName[Str::lower($categoryName)] ?? null;
            if (!$categoryId) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'message' => "Category \"{$categoryName}\" does not match any of your categories. Pick one from the dropdown.",
                ];
                continue;
            }

            $unitId = $this->unitIdsByName[Str::lower($unitName)] ?? null;
            if (!$unitId) {
                $this->errors[] = [
                    'row' => $rowNumber,
                    'message' => "Unit \"{$unitName}\" does not match any of your units. Pick one from the dropdown.",
                ];
                continue;
            }

            $alreadyExists = Product::where('business_id', $this->businessId)
                ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
                ->exists();

            if ($alreadyExists) {
                $this->skipped++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'message' => "A product named \"{$name}\" already exists — skipped.",
                ];
                continue;
            }

            try {
                $this->repo->create([
                    'name'          => $name,
                    'business_id'   => $this->businessId,
                    'user_id'       => $this->userId,
                    'product_code'  => $this->generateProductCode(),
                    'category_id'   => $categoryId,
                    'unit_id'       => $unitId,
                    'buying_price'  => (float) $buyingPrice,
                    'selling_price' => (float) $sellingPrice,
                    'opening_stock' => (float) $openingStock,
                    'branch_ids'    => [$this->branchId],
                ]);

                $this->imported++;
            } catch (Throwable $e) {
                $this->errors[] = ['row' => $rowNumber, 'message' => 'Could not save this row: ' . $e->getMessage()];
            }
        }
    }

    protected function generateProductCode(): string
    {
        do {
            $code = 'IMP-' . Str::upper(Str::random(6));
        } while (Product::where('business_id', $this->businessId)->where('product_code', $code)->exists());

        return $code;
    }

    public function summary(): array
    {
        return [
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
