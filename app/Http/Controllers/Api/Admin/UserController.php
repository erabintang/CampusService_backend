<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * GET /api/admin/users — daftar user (search + pagination + jumlah pesanan).
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->withCount('orders')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 10) ?: 10, 100))
            ->withQueryString();

        return response()->json([
            'data' => collect($users->items())->map(fn (User $user) => $this->present($user)),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/users/{user} — detail user + riwayat pesanan.
     */
    public function show(User $user): JsonResponse
    {
        $orders = $user->orders()->with('product:id,name')->latest()->limit(50)->get();

        return response()->json([
            'data' => array_merge($this->present($user), [
                'orders' => $orders->map(fn ($order) => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'product' => $order->product?->name,
                    'price' => (float) $order->price,
                    'status' => $order->status,
                    'created_at' => optional($order->created_at)->toISOString(),
                ])->values(),
            ]),
        ]);
    }

    /**
     * PUT/PATCH /api/admin/users/{user} — perbarui user.
     * Role sengaja tidak dapat diubah (user biasa tidak bisa jadi admin lewat sini).
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->boolean('status'),
        ]);

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data' => $this->present($user->refresh()),
        ]);
    }

    /**
     * DELETE /api/admin/users/{user} — hapus user (dengan penjagaan aman).
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun sendiri.'], 409);
        }

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Akun admin tidak dapat dihapus.'], 409);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1451) {
                return response()->json([
                    'message' => 'User tidak dapat dihapus karena masih memiliki pesanan.',
                ], 409);
            }

            throw $e;
        }

        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'status' => (bool) $user->status,
            'is_admin' => $user->isAdmin(),
            'orders_count' => (int) $user->orders_count,
            'created_at' => optional($user->created_at)->toISOString(),
        ];
    }
}
