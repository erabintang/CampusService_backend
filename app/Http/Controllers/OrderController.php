<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Tampilkan riwayat pesanan milik user yang sedang login.
     */
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->with(['product.category'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where('order_code', 'like', "%{$search}%");
            })
            ->when(in_array($request->string('status'), Order::STATUSES, true), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    /**
     * Tampilkan detail pesanan milik user.
     *
     * Pesanan milik orang lain dianggap 404 agar keberadaan pesanan tidak
     * bisa ditebak (privasi), bukan sekadar ditolak.
     */
    public function show(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 404);

        $order->load(['product.category']);

        // Nomor WhatsApp diambil dari database (products.whatsapp) via helper bersama.
        $message = "Halo, saya {$order->user->name} dari CampusService.\n"
            . "Saya ingin menanyakan status pesanan saya: {$order->order_code}\n"
            . "Layanan: {$order->product->name}.\n"
            . 'Mohon infonya. Terima kasih!';

        $whatsappUrl = $order->product->whatsappUrl($message);

        return view('orders.show', compact('order', 'whatsappUrl'));
    }
}
