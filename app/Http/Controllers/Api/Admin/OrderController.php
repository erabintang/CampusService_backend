<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * GET /api/admin/orders — daftar semua pesanan (search + filter status).
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['user:id,name,email', 'product:id,name'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(function ($q) use ($search) {
                    $q->where('order_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($request->string('status'), Order::STATUSES, true), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 10) ?: 10, 100))
            ->withQueryString();

        return response()->json([
            'data' => collect($orders->items())->map(fn (Order $order) => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'price' => (float) $order->price,
                'price_formatted' => 'Rp'.number_format((float) $order->price, 0, ',', '.'),
                'status' => $order->status,
                'user' => $order->user ? ['id' => $order->user->id, 'name' => $order->user->name, 'email' => $order->user->email] : null,
                'product' => $order->product ? ['id' => $order->product->id, 'name' => $order->product->name] : null,
                'created_at' => optional($order->created_at)->toISOString(),
            ]),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/orders/{order} — detail pesanan.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['user:id,name,email', 'product:id,name']);

        return response()->json([
            'data' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'price' => (float) $order->price,
                'price_formatted' => 'Rp'.number_format((float) $order->price, 0, ',', '.'),
                'status' => $order->status,
                'user' => $order->user ? ['id' => $order->user->id, 'name' => $order->user->name, 'email' => $order->user->email] : null,
                'product' => $order->product ? ['id' => $order->product->id, 'name' => $order->product->name] : null,
                'created_at' => optional($order->created_at)->toISOString(),
                'updated_at' => optional($order->updated_at)->toISOString(),
            ],
        ]);
    }

    /**
     * PATCH /api/admin/orders/{order}/status — ubah status pesanan.
     * Membatalkan pesanan mengembalikan slot produk; mengaktifkan kembali
     * memakai slot lagi — semua atomik di DB::transaction.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        // ValidationException (mis. slot penuh saat reaktivasi) otomatis
        // menjadi respons JSON 422 oleh exception handler global.
        (new OrderStatusService)->change($order, $request->status);

        return response()->json([
            'message' => "Status pesanan {$order->order_code} diubah menjadi {$request->status}.",
            'data' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'status' => $order->status,
            ],
        ]);
    }
}
