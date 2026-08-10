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
        // MongoDB (Atlas M0 tanpa multi-document transaction): pakai operasi
        // atomik + kompensasi manual — lihat checkoutMongo().
        if (Product::isMongo()) {
            return $this->checkoutMongo($user, $product);
        }

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

        if (Order::isMongo()) {
            // MongoDB tidak mendukung DB::raw — ambil kode hari ini lalu
            // hitung urutan tertinggi di PHP.
            $sequence = Order::query()
                ->where('order_code', 'like', $prefix.'%')
                ->pluck('order_code')
                ->map(fn ($code) => (int) substr((string) $code, strrpos((string) $code, '-') + 1))
                ->max() ?? 0;
        } else {
            // Ambil urutan numerik tertinggi hari ini (robust meski > 9999 pesanan).
            $sequence = (int) Order::query()
                ->where('order_code', 'like', $prefix.'%')
                ->max(DB::raw('CAST(SUBSTRING_INDEX(order_code, "-", -1) AS UNSIGNED)'));
        }

        return sprintf('%s%04d', $prefix, $sequence + 1);
    }

    /**
     * Checkout untuk MongoDB (Atlas M0 TANPA multi-document transaction).
     *
     * Konsistensi dijaga tanpa transaction:
     * 1. Klaim slot secara atomik: $inc stok hanya bila stok > 0 (satu update
     *    dengan filter — dua checkout bersamaan tidak bisa melebihi slot).
     * 2. Simpan order dengan order_code unik (unique index).
     * 3. Bila order gagal tersimpan, slot dikembalikan (kompensasi manual);
     *    retry dengan kode baru hanya untuk duplikat order_code (kode 11000).
     */
    private function checkoutMongo(User $user, Product $product): Order
    {
        foreach (range(1, 5) as $attempt) {
            $fresh = Product::query()->whereKey($product->id)->first();

            if (! $fresh || ! $fresh->status || ! $fresh->category?->status || $fresh->stock < 1) {
                throw ValidationException::withMessages([
                    'product' => 'Maaf, layanan ini tidak dapat dipesan saat ini. Silakan pilih layanan lain.',
                ]);
            }

            // Klaim slot atomik: hanya berkurang bila stok masih tersedia.
            $claimed = Product::query()
                ->whereKey($fresh->id)
                ->where('stock', '>', 0)
                ->update(['$inc' => ['stock' => -1]]);

            if ($claimed !== 1) {
                throw ValidationException::withMessages([
                    'product' => 'Maaf, layanan ini tidak dapat dipesan saat ini. Silakan pilih layanan lain.',
                ]);
            }

            try {
                return $fresh->orders()->create([
                    'user_id' => $user->id,
                    'order_code' => $this->generateOrderCode(),
                    'price' => $fresh->price,
                    'status' => 'pending',
                ]);
            } catch (\Throwable $e) {
                // Order gagal tersimpan -> kembalikan slot agar konsisten.
                Product::query()->whereKey($fresh->id)->update(['$inc' => ['stock' => 1]]);

                if ($this->isDuplicateCode($e) && $attempt < 5) {
                    continue;
                }

                throw $e;
            }
        }

        throw ValidationException::withMessages([
            'product' => 'Gagal membuat pesanan, silakan coba lagi.',
        ]);
    }

    /**
     * Deteksi duplikat unique key lintas driver:
     * 1062 (MySQL) atau 11000 (MongoDB, termasuk WriteError di dalamnya).
     */
    private function isDuplicateCode(\Throwable $e): bool
    {
        $codes = [(int) $e->getCode()];

        if ($e instanceof QueryException && $e->getPrevious() !== null) {
            $previous = $e->getPrevious();
            $codes[] = (int) $previous->getCode();

            if ($previous instanceof \MongoDB\Driver\Exception\BulkWriteException) {
                foreach ($previous->getWriteResult()->getWriteErrors() as $error) {
                    $codes[] = (int) $error->getCode();
                }
            }
        }

        return collect($codes)->contains(fn ($code) => in_array($code, [1062, 11000], true));
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
