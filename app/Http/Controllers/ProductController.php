<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display the public catalog of active products with search and filters.
     */
    public function index(Request $request)
    {
        $products = Product::where('status', true)
            ->fromActiveCategory()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->input('category_id'));
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Display the specified product with related services and real order stats.
     */
    public function show(Product $product)
    {
        // Hanya produk aktif dari kategori aktif yang boleh dilihat publik.
        abort_unless($product->status, 404);
        abort_unless($product->category?->status, 404);

        $product->load('category');

        // Layanan serupa dari kategori yang sama untuk section "Layanan Serupa".
        $relatedProducts = Product::query()
            ->where('status', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->fromActiveCategory()
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();

        // Jumlah pesanan selesai — statistik real dari MySQL untuk social proof.
        $soldCount = $product->orders()->where('status', 'completed')->count();

        return view('products.show', compact('product', 'relatedProducts', 'soldCount'));
    }
}
