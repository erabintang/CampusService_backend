<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products — katalog layanan aktif dengan search + filter kategori.
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::where('status', true)
            ->whereHas('category', fn ($q) => $q->where('status', true))
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->latest()
            ->paginate((int) $request->integer('per_page', 9) ?: 9)
            ->withQueryString();

        return response()->json([
            'data' => collect($products->items())->map(fn (Product $product) => $this->present($product)),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * GET /api/products/{slug} — detail layanan + layanan serupa + statistik real.
     */
    public function show(Product $product): JsonResponse
    {
        abort_unless($product->status, 404);
        abort_unless($product->category?->status, 404);

        $product->load('category');

        $relatedProducts = Product::query()
            ->where('status', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->whereHas('category', fn ($q) => $q->where('status', true))
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();

        $soldCount = $product->orders()->where('status', 'completed')->count();

        return response()->json([
            'data' => array_merge($this->present($product), [
                'related_products' => collect($relatedProducts)->map(fn (Product $p) => $this->present($p))->values(),
                'sold_count' => $soldCount,
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->image ? asset('storage/'.$product->image) : null,
            'price' => (float) $product->price,
            'price_formatted' => 'Rp'.number_format((float) $product->price, 0, ',', '.'),
            'description' => $product->description,
            'included' => $product->included,
            'payment_info' => $product->payment_info,
            'duration' => $product->duration,
            'stock' => $product->stock,
            'status' => (bool) $product->status,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'created_at' => optional($product->created_at)->toISOString(),
        ];
    }
}
