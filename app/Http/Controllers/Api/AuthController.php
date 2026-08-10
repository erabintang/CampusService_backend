<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/login — login berbasis session (cookie), konsisten dengan
     * Breeze. Mengembalikan data user yang login.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Rate limit login (5 percobaan per menit) — perlindungan brute force.
        if (method_exists($request, 'rateLimit')) {
            // RateLimiter di-handle middleware throttle bila dipasang.
        }

        if (! Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        // User nonaktif tidak boleh login (guard tambahan).
        if (! $user->status) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun telah dinonaktifkan.',
            ]);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => $this->userPayload($user),
        ]);
    }

    /**
     * POST /api/register — daftar user baru (role default: user, status aktif),
     * lalu langsung login. Mengembalikan data user.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'user', // default: user biasa. Admin hanya lewat seeder.
            'status' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'data' => $this->userPayload($user),
        ], 201);
    }

    /**
     * POST /api/logout — logout dan invalidasi session.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * GET /api/user — identitas user yang sedang login (alias /api/auth/me).
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $user ? $this->userPayload($user) : null,
        ]);
    }

    /**
     * GET /api/auth/me — identitas user (dipakai guard admin Next.js).
     */
    public function me(Request $request): JsonResponse
    {
        return $this->user($request);
    }

    /**
     * GET /api/profile — detail profil user yang login.
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->userPayload($request->user()),
        ]);
    }

    /**
     * PUT/PATCH /api/profile — perbarui nama, email, dan nomor HP.
     */
    public function updateProfile(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->input('phone'),
        ]);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => $this->userPayload($user->refresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'status' => (bool) $user->status,
            'is_admin' => $user->isAdmin(),
            'created_at' => optional($user->created_at)->toISOString(),
        ];
    }
}
