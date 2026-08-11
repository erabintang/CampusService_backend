<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * GET /api/categories — daftar kategori aktif dengan jumlah produk.
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get();

        $categories->loadCount(['products' => fn ($q) => $q->where('status', true)]);

        return response()->json([
            'data' => $categories->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'products_count' => $category->products_count,
            ])->values(),
        ]);
    }
}
