<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeleteErrors;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    use HandlesDeleteErrors;

    protected $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', Category::class);
        return response()->json($this->service->getAllCategories());
    }

    public function store(CategoryRequest $request)
    {
        $this->authorize('create', Category::class);
        $user                = Auth::user();
        $data                = $request->validated();
        $data['user_id']     = $user->id;
        $data['business_id'] = $user->business_id;
        $category            = $this->service->createCategory($data);
        return response()->json([
            'message' => 'Category created successfully',
            'data'    => $category,
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $category = $this->service->getCategory($id);
        $this->authorize('update', $category);
        $category = $this->service->updateStatus($id, $request->status);
        return response()->json([
            'message' => 'Category status updated',
            'data'    => $category,
        ]);
    }

    public function updateCategory($id, CategoryRequest $request)
    {
        $category = $this->service->getCategory($id);
        $this->authorize('update', $category);
        $category = $this->service->updateCategory($id, $request->validated());
        return response()->json([
            'message' => 'Category updated successfully',
            'data'    => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = $this->service->getCategory($id);
        $this->authorize('delete', $category);
        return $this->safeDelete(fn () => $this->service->deleteCategory($id), 'Category deleted successfully');
    }
}
