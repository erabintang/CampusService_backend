<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    /**
     * Show the public home page with hero, categories and latest services.
     */
    public function index()
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get();

        $categories->loadCount(['products' => fn ($q) => $q->where('status', true)]);

        $latestProducts = Product::where('status', true)
            ->fromActiveCategory()
            ->with('category')
            ->latest()
            ->limit(8)
            ->get();

        $activeProductCount = Product::where('status', true)->count();

        return view('home.index', compact('categories', 'latestProducts', 'activeProductCount'));
    }
}
