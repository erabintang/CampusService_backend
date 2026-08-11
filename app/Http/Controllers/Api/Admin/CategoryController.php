<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * GET /api/admin/categories — daftar kategori (search + pagination).
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 10) ?: 10, 100))
            ->withQueryString();

        $categories->getCollection()->loadCount('products');

        return response()->json([
            'data' => collect($categories->items())->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'status' => (bool) $category->status,
                'products_count' => $category->products_count,
                'created_at' => optional($category->created_at)->toISOString(),
            ]),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/categories — tambah kategori.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->boolean('status'),
        ]);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $this->present($category),
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/categories/{category} — perbarui kategori.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->boolean('status'),
        ]);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $this->present($category->refresh()),
        ]);
    }

    /**
     * DELETE /api/admin/categories/{category} — hapus kategori (FK RESTRICT aman).
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            $category->delete();
        } catch (QueryException $e) {
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1451) {
                return response()->json([
                    'message' => 'Kategori tidak dapat dihapus karena masih memiliki produk.',
                ], 409);
            }

            throw $e;
        }

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'status' => (bool) $category->status,
            'created_at' => optional($category->created_at)->toISOString(),
        ];
    }
}
