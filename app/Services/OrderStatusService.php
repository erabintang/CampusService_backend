<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Ubah status pesanan beserta penyesuaian slot stok, dibungkus DB::transaction.
 */
class OrderStatusService
{
    public function change(Order $order, string $newStatus): void
    {
        $oldStatus = $order->status;

        if ($oldStatus === $newStatus) {
            return;
        }

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
    }
}
