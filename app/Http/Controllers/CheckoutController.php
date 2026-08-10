<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout)
    {
    }

    /**
     * Tampilkan form konfirmasi pemesanan.
     */
    public function create(Product $product): View
    {
        abort_unless($product->status, 404);
        abort_unless($product->category?->status, 404);

        $product->load('category');

        return view('orders.checkout', compact('product'));
    }

    /**
     * Simpan pesanan, kurangi slot, lalu arahkan ke WhatsApp penyedia.
     *
     * Order + pengurangan stok dibungkus DB::transaction di CheckoutService
     * agar selalu konsisten: tidak mungkin order tersimpan tapi stok gagal
     * berkurang, atau sebaliknya.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status, 404);

        try {
            $order = $this->checkout->checkout($request->user(), $product);
        } catch (ValidationException $e) {
            return redirect()
                ->route('products.show', $product)
                ->with('error', $e->errors()['product'][0] ?? 'Layanan tidak dapat dipesan saat ini.');
        }

        return redirect()->away($this->checkout->whatsappUrl($order));
    }
}
