<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\RelationCounts;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * GET /api/admin/dashboard — statistik real dari database.
     */
    public function index(): JsonResponse
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalUsers = User::where('role', 'user')->count();
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('price');

        $statusCounts = collect(Order::STATUSES)->mapWithKeys(fn ($status) => [
            $status => Order::where('status', $status)->count(),
        ]);

        // Pesanan per hari untuk 7 hari terakhir (termasuk hari tanpa pesanan = 0).
        $dailyCounts = Order::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn ($order) => $order->created_at->format('Y-m-d'))
            ->map->count();

        $dailyOrders = collect(range(6, 0))->mapWithKeys(function ($i) use ($dailyCounts) {
            $date = now()->subDays($i)->format('Y-m-d');

            return [$date => (int) ($dailyCounts[$date] ?? 0)];
        });

        // Pendapatan per status.
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

        // Top 5 layanan terlaris (pesanan tidak dibatalkan).
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
                ->get(['id', 'name', 'slug', 'stock']);
        }

        $latestOrders = Order::with(['user:id,name', 'product:id,name'])
            ->latest()
            ->limit(5)
            ->get();

        $lowStockProducts = Product::where('stock', '<=', 3)
            ->orderBy('stock')
            ->limit(5)
            ->get(['id', 'name', 'slug', 'stock']);

        return response()->json([
            'data' => [
                'totals' => [
                    'products' => $totalProducts,
                    'categories' => $totalCategories,
                    'users' => $totalUsers,
                    'orders' => $totalOrders,
                    'revenue' => (float) $totalRevenue,
                    'revenue_formatted' => 'Rp'.number_format((float) $totalRevenue, 0, ',', '.'),
                ],
                'status_counts' => $statusCounts,
                'daily_orders' => $dailyOrders,
                'revenue_by_status' => $revenueByStatus,
                'top_products' => $topProducts->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'stock' => $p->stock,
                    'orders_count' => $p->orders_count,
                ])->values(),
                'latest_orders' => $latestOrders->map(fn (Order $o) => [
                    'id' => $o->id,
                    'order_code' => $o->order_code,
                    'status' => $o->status,
                    'price' => (float) $o->price,
                    'user' => $o->user?->name,
                    'product' => $o->product?->name,
                    'created_at' => optional($o->created_at)->toISOString(),
                ])->values(),
                'low_stock_products' => $lowStockProducts->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'stock' => $p->stock,
                ])->values(),
            ],
        ]);
    }
}
