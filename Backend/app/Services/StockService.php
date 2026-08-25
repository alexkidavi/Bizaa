<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBranch;
use App\Repositories\StockRepository;
use App\Models\StockHistory;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
class StockService
{
    protected $repo;

    public function __construct(StockRepository $repo, protected DigitaxService $digitax)
    {
        $this->repo = $repo;
    }

   public function getAllStock()
    {
        return $this->repo->getAllStock();
    }
    public function getByProduct($id)
    {
        return $this->repo->getByProduct($id);
    }

    // ✅ CREATE (Register stock)
public function stockTake(array $data)
{
    return DB::transaction(function () use ($data) {

        // ✅ 1. Validate input (important safety guard)
        if (empty($data['branch_ids'])) {
            throw new \Exception("Branch IDs are required for stock take.");
        }

        // ✅ 2. Create stock take header
        $stockTake = StockTake::create([
            'name' => $data['name'],
            'reference' => 'STC' . strtoupper(Str::random(8)),
            'description' => $data['description'] ?? null,
            'created_by' => Auth::id(),
            'business_id' => Auth::user()->business_id,
            'user_id' => Auth::id(),
        ]);
        // ✅ 3. Build query safely (FIX: handle empty category_ids)
        $stocksQuery = ProductBranch::with('product')
            ->whereIn('branch_id', $data['branch_ids']);

        if (!empty($data['category_ids'])) {
            $stocksQuery->whereHas('product', function ($q) use ($data) {
                $q->whereIn('category_id', $data['category_ids']);
            });
        }

        $stocks = $stocksQuery->get();

    

        // ⚠️ Optional safety check (helps debugging empty results)
        if ($stocks->isEmpty()) {
            info('No stock records found for given filters.');
        }

        // ✅ 4. Save snapshot into stock_take_items
        foreach ($stocks as $stock) {
            StockTakeItem::create([
                'business_id'    => Auth::user()->business_id,
                'user_id'        => Auth::id(),
                'stock_take_id'  => $stockTake->id,
                'product_id'     => $stock->product_id,
                'branch_id'      => $stock->branch_id,
                'recorded_stock' => $stock->stock,
            ]);
        }

        return $stockTake->load('items');
    });
}

    public function create($data)
    {
        $record = ProductBranch::create([
            'product_id' => $data['product_id'],
            'branch_id' => $data['branch_id'],
            'stock' => $data['stock'] ?? 0,
        ]);

        $user = Auth::user();

        StockHistory::create([
            'product_id' => $data['product_id'],
            'branch_id' => $data['branch_id'],
            'business_id' => $user->business_id,
            'user_id' => $user->id,
            'type' => 'creation',
            'action' => 'set',
            'quantity' => $record->stock,
            'notes' => $data['notes'] ?? 'Initial stock registration',
        ]);

        return $record;
    }

    // ✅ UPDATE
    public function update($id, $data)
    {
        $record = ProductBranch::findOrFail($id);

        $oldStock = $record->stock;
        $newStock = $data['stock'] ?? $oldStock;

        $record->update([
            'stock' => $newStock
        ]);

        $user = Auth::user();

        StockHistory::create([
            'product_id' => $record->product_id,
            'branch_id' => $record->branch_id,
            'business_id' => $user->business_id,
            'user_id' => $user->id,
            'type' => 'update',
            'action' => 'set',
            'quantity' => $newStock,
            'notes' => $data['notes'] ?? 'Stock updated',
        ]);

        return $record;
    }

    // ✅ DELETE
  public function delete($id)
{
    $record = StockTake::findOrFail($id);

    // delete child records first
    StockTakeItem::where('stock_take_id', $record->id)->delete();

    // delete parent
    $record->delete();

    return response()->json([
        'message' => 'Stock take deleted successfully'
    ]);
}

    // ✅ ADJUST (already good, kept as-is)
    public function adjust($productId, $data)
    {
        $productId=Crypt::decryptString($productId);
        $branchId = $data['branch_id'];
        $qty = (int) $data['quantity'];
        $action = $data['action'];
        $notes = $data['reason'] ?? null;

        $product = Product::findOrFail($productId);

        $record = ProductBranch::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        if (!$record) {
                $record = ProductBranch::create([
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'buying_price' => $product->buying_price,
                    'selling_price' => $product->selling_price,
                    'stock' => 0,
                ]);
        }

        $oldStock = $record->stock;

        if ($action === 'set') {
            $record->stock = $qty;
        } elseif ($action === 'add') {
            $record->stock += $qty;
        } elseif ($action === 'remove') {
            $record->stock -= $qty;
        }

        if ($record->stock < 0) {
            $record->stock = 0;
        }

        $record->save();

        $user = Auth::user();

        $history = StockHistory::create([
            'product_id' => $productId,
            'branch_id' => $branchId,
            'business_id' => $user->business_id,
            'user_id' => $user->id,
            'reference' => 'ADJ-' . strtoupper(uniqid()),
            'type' => 'adjustment',
            'action' => $action,
            'quantity' => $qty,
            'notes' => $notes,
        ]);

        // Business-wide stock delta, same as the POS sale flow decrements
        // directly at checkout — DigiTax's ledger has no branch concept.
        $delta = $record->stock - $oldStock;
        $this->digitax->syncStockAdjustment($product, $history, (float) $delta);

        return $record;
    }
public function getStockTakeById($id)
{
    $stockTake = StockTake::with([
        // 'branches:id,name',
        // 'categories:id,name',
    ])->findOrFail($id);

    // Summary counts for the hero card
    $stockTake->counted_count   = StockTakeItem::where('stock_take_id', $id)
                                    ->whereNotNull('counted_stock')
                                    ->count();

    $stockTake->uncounted_count = StockTakeItem::where('stock_take_id', $id)
                                    ->whereNull('counted_stock')
                                    ->count();

    $stockTake->variance_count  = StockTakeItem::where('stock_take_id', $id)
                                    ->whereNotNull('counted_stock')
                                    ->whereRaw('counted_stock != recorded_stock')
                                    ->count();

    return $stockTake;
}


public function getStockTakeItems(Request $request, $id)
{
    $perPage  = $request->get('per_page', 15);
    $search   = $request->get('search', '');
    $branch   = $request->get('branch_id', '');
    $category = $request->get('category_id', '');
    $status   = $request->get('status', '');

    $query = DB::table('stock_take_items as sti')
        ->join('products as p', 'p.id', '=', 'sti.product_id')
        ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
        ->leftJoin('branches as b', 'b.id', '=', 'sti.branch_id') // Join branch from stock_take_items
        ->where('sti.stock_take_id', $id)
        ->select(
            'sti.*',
            'p.id as product_id',
            'p.name as product_name',
            'p.product_code',
            'p.category_id',
            'c.name as category_name',
            'sti.branch_id', // Use branch_id from stock_take_items
            'b.name as branch_name'
        );

    // Apply branch filter FIRST (important for performance)
    if ($branch) {
        $query->where('sti.branch_id', $branch);
    }

    // Search (product name or code)
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('p.name', 'like', "%{$search}%")
              ->orWhere('p.product_code', 'like', "%{$search}%");
        });
    }

    // Category filter
    if ($category) {
        $query->where('p.category_id', $category);
    }

    // Status filter
    if ($status === 'counted') {
        $query->whereNotNull('sti.counted_stock');
    } elseif ($status === 'uncounted') {
        $query->whereNull('sti.counted_stock');
    } elseif ($status === 'variance') {
        $query->whereNotNull('sti.counted_stock')
              ->whereRaw('sti.counted_stock != sti.recorded_stock');
    }

    // Add ordering to ensure consistent pagination
    $query->orderBy('sti.id');

    return $query->paginate($perPage);
}

public function transferStock(array $data)
{
     return $this->repo->transferStock($data);
   
}
public function getTransfers()
{
     return $this->repo->getTransfers();
   
}
}