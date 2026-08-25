<?php

namespace App\Services;

use App\Exports\ProductImportTemplateExport;
use App\Imports\ProductsImport;
use App\Repositories\ProductRepository;
use App\Services\DigitaxService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ProductService
{
    protected $repo;

    public function __construct(ProductRepository $repo, protected DigitaxService $digitax)
    {
        $this->repo = $repo;
    }

    public function downloadTemplate()
    {
        $businessId = Auth::user()->business_id;

        return Excel::download(
            new ProductImportTemplateExport($businessId),
            'product-import-template.xlsx'
        );
    }

    public function importProducts(UploadedFile $file, int $branchId): array
    {
        $user = Auth::user();

        $import = new ProductsImport($this->repo, $user->business_id, $user->id, $branchId);
        Excel::import($import, $file);

        return $import->summary();
    }

    public function getAllProducts()
    {
        return $this->repo->all();
    }

    public function getProduct($id)
    {
        return $this->repo->find($id);
    }
    

    public function createProduct(array $data)
    {
        $product = $this->repo->create($data);
        $this->digitax->syncItem($product);

        return $product;
    }

    public function updateProduct($id, array $data)
    {
        $product = $this->repo->update($id, $data);
        $this->digitax->syncItem($product);

        return $product;
    }

    public function deleteProduct($id)
    {
        return $this->repo->delete($id);
    }
}
