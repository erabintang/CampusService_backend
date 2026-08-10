<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Support\RelationCounts;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the users with search and pagination.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        RelationCounts::attachCount($users->getCollection(), 'orders', 'user_id', 'orders_count');

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user with their order history.
     */
    public function show(User $user)
    {
        $orders = $user->orders()
            ->with('product')
            ->latest()
            ->paginate(10);

        return view('admin.users.show', compact('user', 'orders'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     *
     * Role is intentionally not editable: a normal user can never be
     * turned into an admin from this screen.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->boolean('status'),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     *
     * Admin accounts, the currently logged-in admin, and users that still
     * have orders (FK RESTRICT) cannot be deleted.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->isAdmin()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Akun admin tidak dapat dihapus.');
        }

        // MongoDB tidak punya FK constraint — cek manual agar perilaku sama.
        if (User::isMongo() && $user->orders()->exists()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'User tidak dapat dihapus karena masih memiliki pesanan.');
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            // 1451 = foreign key constraint violation (user masih punya pesanan)
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1451) {
                return redirect()
                    ->route('admin.users.index')
                    ->with('error', 'User tidak dapat dihapus karena masih memiliki pesanan.');
            }

            throw $e;
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
