<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the products with search, filters and pagination.
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->boolean('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        // Buang 'image' dari data: hanya diisi jika file benar-benar dikirim.
        $data = Arr::except($request->validated(), 'image');
        $data['slug'] = Product::generateUniqueSlug($request->name);
        $data['status'] = $request->boolean('status');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        // Buang 'image' agar gambar lama tidak terhapus saat tidak ada file baru.
        $data = Arr::except($request->validated(), 'image');
        $data['status'] = $request->boolean('status');

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified product from storage.
     *
     * A product that already has orders cannot be deleted (FK RESTRICT).
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
        } catch (QueryException $e) {
            // 1451 = foreign key constraint violation (produk masih punya pesanan)
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1451) {
                return redirect()
                    ->route('admin.products.index')
                    ->with('error', 'Produk tidak dapat dihapus karena sudah memiliki pesanan.');
            }

            throw $e;
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
