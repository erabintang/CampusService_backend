<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
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
     * Order + pengurangan stok dibungkus DB::transaction agar selalu konsisten:
     * tidak mungkin order tersimpan tapi stok gagal berkurang, atau sebaliknya.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status, 404);

        $user = $request->user();

        // Order + pengurangan stok dibungkus transaction agar selalu konsisten.
        // Retry kecil untuk race kode pesanan: dua checkout (produk berbeda) yang
        // bersamaan bisa menghasilkan kode yang sama; constraint UNIQUE menolak
        // duplikat, lalu kita coba sekali lagi dengan kode baru.
        $result = null;

        foreach (range(1, 5) as $attempt) {
            try {
                $result = DB::transaction(function () use ($product, $user) {
                    // Kunci baris produk supaya dua checkout produk yang sama
                    // tidak melebihi slot (lockForUpdate).
                    $locked = Product::query()->whereKey($product->id)->lockForUpdate()->first();

                    if (! $locked || ! $locked->status || ! $locked->category?->status || $locked->stock < 1) {
                        return redirect()
                            ->route('products.show', $product)
                            ->with('error', 'Maaf, layanan ini tidak dapat dipesan saat ini. Silakan pilih layanan lain.');
                    }

                    $order = $locked->orders()->create([
                        'user_id' => $user->id,
                        'order_code' => $this->generateOrderCode(),
                        'price' => $locked->price,
                        'status' => 'pending',
                    ]);

                    $locked->decrement('stock');

                    return $order;
                });

                break;
            } catch (QueryException $e) {
                // errorInfo[1] === 1062 = duplicate entry (MySQL). Bukan duplikat
                // kode pesanan? Maka biarkan exception asli naik.
                $isDuplicateCode = ($e->errorInfo[1] ?? null) === 1062;

                if (! $isDuplicateCode || $attempt === 5) {
                    throw $e;
                }
            }
        }

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        return redirect()->away($this->whatsappUrl($result));
    }

    /**
     * Generate kode pesanan unik dengan format ORD-YYYYMMDD-NNNN.
     *
     * Contoh: ORD-20260808-0001, ORD-20260808-0002, dst.
     */
    private function generateOrderCode(): string
    {
        $date = now()->format('Ymd');
        $prefix = "ORD-{$date}-";

        // Ambil urutan numerik tertinggi hari ini (robust meski > 9999 pesanan).
        $sequence = (int) Order::query()
            ->where('order_code', 'like', $prefix.'%')
            ->max(DB::raw('CAST(SUBSTRING_INDEX(order_code, "-", -1) AS UNSIGNED)'));

        return sprintf('%s%04d', $prefix, $sequence + 1);
    }

    /**
     * Bangun pesan WhatsApp otomatis berisi kode pesanan, lalu URL-nya
     * lewat helper bersama Product::whatsappUrl() (nomor dari database).
     */
    private function whatsappUrl(Order $order): string
    {
        $product = $order->product;
        $price = 'Rp'.number_format((float) $order->price, 0, ',', '.');
        $message = "Halo, saya {$order->user->name} dari CampusService.\n"
            . "Saya ingin memesan layanan: {$product->name} ({$price}).\n"
            . "Kode pesanan saya: {$order->order_code}.\n"
            . 'Mohon info selanjutnya. Terima kasih!';

        return $product->whatsappUrl($message);
    }
}
