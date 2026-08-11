<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Logika pemesanan bersama — dipakai oleh CheckoutController (Blade) dan
 * Api\OrderController (JSON). Menjamin order + pengurangan stok konsisten
 * (DB::transaction) dan kode pesanan unik (ORD-YYYYMMDD-NNNN).
 */
class CheckoutService
{
    /**
     * Proses checkout: simpan order, kurangi slot, kembalikan Order.
     *
     * Melempar ValidationException bila layanan tidak bisa dipesan.
     */
    public function checkout(User $user, Product $product): Order
    {
        // Order + pengurangan stok dibungkus transaction agar selalu konsisten.
        // Retry kecil untuk race kode pesanan: dua checkout yang bersamaan bisa
        // menghasilkan kode yang sama; constraint UNIQUE menolak duplikat, lalu
        // kita coba sekali lagi dengan kode baru.
        $result = null;

        foreach (range(1, 5) as $attempt) {
            try {
                $result = DB::transaction(function () use ($product, $user) {
                    // Kunci baris produk supaya dua checkout produk yang sama
                    // tidak melebihi slot (lockForUpdate).
                    $locked = Product::query()->whereKey($product->id)->lockForUpdate()->first();

                    if (! $locked || ! $locked->status || ! $locked->category?->status || $locked->stock < 1) {
                        throw ValidationException::withMessages([
                            'product' => 'Maaf, layanan ini tidak dapat dipesan saat ini. Silakan pilih layanan lain.',
                        ]);
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

        return $result;
    }

    /**
     * Generate kode pesanan unik dengan format ORD-YYYYMMDD-NNNN.
     *
     * Contoh: ORD-20260808-0001, ORD-20260808-0002, dst.
     */
    public function generateOrderCode(): string
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
    public function whatsappUrl(Order $order): string
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
