<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * GET /api/admin/products — daftar produk (search + filter kategori/status).
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with('category:id,name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('search')->trim().'%');
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->boolean('status'));
            })
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 10) ?: 10, 100))
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
     * POST /api/admin/products — tambah produk (gambar opsional).
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = Arr::except($request->validated(), 'image');
        $data['slug'] = Product::generateUniqueSlug($request->name);
        $data['status'] = $request->boolean('status');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'data' => $this->present($product->load('category:id,name')),
        ], 201);
    }

    /**
     * GET /api/admin/products/{product} — detail produk.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $this->present($product->load('category:id,name')),
        ]);
    }

    /**
     * PUT/PATCH /api/admin/products/{product} — perbarui produk.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = Arr::except($request->validated(), 'image');
        $data['status'] = $request->boolean('status');

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'data' => $this->present($product->load('category:id,name')),
        ]);
    }

    /**
     * DELETE /api/admin/products/{product} — hapus produk (FK RESTRICT aman).
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $product->delete();
        } catch (QueryException $e) {
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1451) {
                return response()->json([
                    'message' => 'Produk tidak dapat dihapus karena sudah memiliki pesanan.',
                ], 409);
            }

            throw $e;
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        return response()->json(['message' => 'Produk berhasil dihapus.']);
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
            'category_id' => $product->category_id,
            'category' => $product->category?->name,
            'price' => (float) $product->price,
            'price_formatted' => 'Rp'.number_format((float) $product->price, 0, ',', '.'),
            'description' => $product->description,
            'included' => $product->included,
            'payment_info' => $product->payment_info,
            'duration' => $product->duration,
            'stock' => $product->stock,
            'whatsapp' => $product->whatsapp,
            'status' => (bool) $product->status,
            'created_at' => optional($product->created_at)->toISOString(),
        ];
    }
}
