<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout)
    {
    }

    /**
     * GET /api/orders — riwayat pesanan milik user yang login (search + filter status).
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with('product.category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('order_code', 'like', '%'.$request->string('search')->trim().'%');
            })
            ->when(in_array($request->string('status'), Order::STATUSES, true), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate((int) $request->integer('per_page', 10) ?: 10)
            ->withQueryString();

        return response()->json([
            'data' => collect($orders->items())->map(fn (Order $order) => $this->present($order)),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * POST /api/orders — checkout layanan.
     *
     * Body: { product_id, checksum? } — memakai CheckoutService yang sama
     * dengan Blade (transaction + stok + kode pesanan).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        try {
            $order = $this->checkout->checkout($request->user(), $product);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors()['product'][0] ?? 'Layanan tidak dapat dipesan saat ini.',
                'errors' => $e->errors(),
            ], 422);
        }

        $order->load(['user', 'product.category']);

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'data' => array_merge($this->present($order), [
                'whatsapp_url' => $this->checkout->whatsappUrl($order),
            ]),
        ], 201);
    }

    /**
     * GET /api/orders/{order} — detail pesanan milik user (IDOR → 404).
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        // Privasi: hanya pemilik pesanan yang boleh melihat detail.
        if ($order->user_id !== $request->user()->id) {
            abort(404);
        }

        $order->load(['user', 'product.category']);

        return response()->json([
            'data' => array_merge($this->present($order), [
                'whatsapp_url' => $this->checkout->whatsappUrl($order),
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'price' => (float) $order->price,
            'price_formatted' => 'Rp'.number_format((float) $order->price, 0, ',', '.'),
            'status' => $order->status,
            'created_at' => optional($order->created_at)->toISOString(),
            'updated_at' => optional($order->updated_at)->toISOString(),
            'user' => $order->user ? [
                'id' => $order->user->id,
                'name' => $order->user->name,
                'email' => $order->user->email,
            ] : null,
            'product' => $order->product ? [
                'id' => $order->product->id,
                'name' => $order->product->name,
                'slug' => $order->product->slug,
                'price' => (float) $order->product->price,
                'category' => $order->product->category?->name,
                'whatsapp' => $order->product->whatsapp,
            ] : null,
        ];
    }
}
