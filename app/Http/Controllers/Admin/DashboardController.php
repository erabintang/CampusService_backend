<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\RelationCounts;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard with real statistics from the database.
     */
    public function index()
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalUsers = User::where('role', 'user')->count();
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('price');

        $statusCounts = [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // Jumlah pesanan per hari untuk 7 hari terakhir (termasuk hari tanpa pesanan = 0).
        $dailyCounts = Order::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn ($order) => $order->created_at->format('Y-m-d'))
            ->map->count();

        $dailyOrders = collect(range(6, 0))->mapWithKeys(function ($i) use ($dailyCounts) {
            $date = now()->subDays($i)->format('Y-m-d');

            return [$date => (int) ($dailyCounts[$date] ?? 0)];
        });

        // Pendapatan (total harga) per status pesanan.
        if (Order::isMongo()) {
            // MongoDB: selectRaw/groupBy tidak didukung -> agregasi $group native.
            $revenueByStatus = collect(Order::STATUSES)->mapWithKeys(fn ($status) => [$status => 0.0]);

            foreach (Order::raw(fn ($collection) => $collection->aggregate([
                ['$group' => ['_id' => '$status', 'total' => ['$sum' => '$price']]],
            ])) as $row) {
                $revenueByStatus[$row['_id']] = (float) $row['total'];
            }
        } else {
            $revenueByStatus = Order::query()
                ->selectRaw('status, SUM(price) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $revenueByStatus = collect(Order::STATUSES)->mapWithKeys(
                fn ($status) => [$status => (float) ($revenueByStatus[$status] ?? 0)]
            );
        }

        // Top 5 layanan terlaris (pesanan yang tidak dibatalkan).
        if (Product::isMongo()) {
            $topProducts = Product::all();
            RelationCounts::attachCount(
                $topProducts,
                'orders',
                'product_id',
                'orders_count',
                fn ($query) => $query->where('status', '!=', 'cancelled')
            );
            $topProducts = $topProducts->sortByDesc('orders_count')->take(5)->values();
        } else {
            $topProducts = Product::withCount(['orders' => fn ($query) => $query->where('status', '!=', 'cancelled')])
                ->orderByDesc('orders_count')
                ->limit(5)
                ->get();
        }

        $latestOrders = Order::with(['user', 'product'])
            ->latest()
            ->limit(5)
            ->get();

        $lowStockProducts = Product::where('stock', '<=', 3)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalUsers',
            'totalOrders',
            'totalRevenue',
            'statusCounts',
            'dailyOrders',
            'revenueByStatus',
            'topProducts',
            'latestOrders',
            'lowStockProducts',
        ));
    }
}
