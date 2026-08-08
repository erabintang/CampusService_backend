<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders with search, filters and pagination.
     */
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['user', 'product'])
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
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'product.category']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the status of the specified order.
     *
     * When an order is cancelled, the product slot (stock) is restored
     * so the slot can be booked by another user again.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Perubahan status + penyesuaian slot dilakukan atomik (DB::transaction)
        // agar stok dan status selalu konsisten (tidak ada yang gagal separuh).
        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                // Order dibatalkan: slot dibebaskan kembali.
                $order->product()->increment('stock');
            } elseif ($newStatus !== 'cancelled' && $oldStatus === 'cancelled') {
                // Order diaktifkan kembali: slot dipakai lagi (jika masih tersedia).
                $order->product()->where('stock', '>', 0)->decrement('stock');
            }

            $order->update(['status' => $newStatus]);
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', "Status pesanan {$order->order_code} diubah menjadi {$newStatus}.");
    }
}
