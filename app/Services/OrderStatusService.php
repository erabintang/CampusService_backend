<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Ubah status pesanan beserta penyesuaian slot stok.
 *
 * - SQL: dibungkus DB::transaction (perilaku lama — TIDAK berubah).\n
 * - MongoDB (Atlas M0 TANPA multi-document transaction): operasi atomik
 *   $inc + kompensasi manual bila update status gagal.
 */
class OrderStatusService
{
    public function change(Order $order, string $newStatus): void
    {
        $oldStatus = $order->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        if (Order::isMongo()) {
            $this->changeMongo($order, $oldStatus, $newStatus);

            return;
        }

        $this->changeSql($order, $oldStatus, $newStatus);
    }

    private function changeSql(Order $order, string $oldStatus, string $newStatus): void
    {
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

    private function changeMongo(Order $order, string $oldStatus, string $newStatus): void
    {
        $stockChanged = false;

        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            // Slot dibebaskan kembali ($inc atomik).
            $order->product()->increment('stock');
            $stockChanged = true;
        } elseif ($newStatus !== 'cancelled' && $oldStatus === 'cancelled') {
            // Slot dipakai lagi — hanya bila masih tersedia (filter + $inc atomik).
            $affected = $order->product()->where('stock', '>', 0)->decrement('stock');

            if ($affected !== 1) {
                throw ValidationException::withMessages([
                    'status' => 'Slot layanan sudah penuh, pesanan tidak dapat diaktifkan kembali.',
                ]);
            }

            $stockChanged = true;
        }

        try {
            $order->update(['status' => $newStatus]);
        } catch (\Throwable $e) {
            // Kompensasi manual: kembalikan stok seperti semula.
            if ($stockChanged) {
                if ($newStatus === 'cancelled') {
                    $order->product()->decrement('stock');
                } else {
                    $order->product()->increment('stock');
                }
            }

            throw $e;
        }
    }
}
