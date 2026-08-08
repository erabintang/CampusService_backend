<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Temukan Layanan Mahasiswa') }} — CampusService</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gray-50 text-gray-800 antialiased">
    {{-- ===== NAVBAR ===== --}}
    <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">CS</span>
                <span class="text-lg font-bold text-gray-900">Campus<span class="text-indigo-600">Service</span></span>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-medium text-gray-600 md:flex">
                <a href="{{ route('home') }}" class="transition hover:text-indigo-600 {{ request()->routeIs('home') ? 'text-indigo-600' : '' }}">Beranda</a>
                <a href="{{ route('products.index') }}" class="transition hover:text-indigo-600 {{ request()->routeIs('products.*') ? 'text-indigo-600' : '' }}">Layanan</a>
            </nav>

            <div class="flex flex-wrap items-center justify-end gap-1 sm:gap-2">
                @auth
                    <a href="{{ route('orders.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:text-indigo-600 {{ request()->routeIs('orders.*') ? 'text-indigo-600' : '' }}">
                        Pesanan
                    </a>
                    <a href="{{ route('profile.edit') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:text-indigo-600 {{ request()->routeIs('profile.*') ? 'text-indigo-600' : '' }}">
                        Profil
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">
                            Admin Panel
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:text-indigo-600">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- ===== KONTEN ===== --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ===== FOOTER ===== --}}
    <footer class="border-t border-gray-200 bg-white">
        {{-- pb-24 memberi ruang untuk sticky bottom bar halaman detail di mobile --}}
        <div class="mx-auto max-w-7xl px-4 py-8 pb-24 sm:px-6 lg:px-8 lg:pb-8">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-indigo-600 text-xs font-bold text-white">CS</span>
                    <span class="text-sm font-semibold text-gray-700">CampusService</span>
                </div>
                <p class="text-xs text-gray-400">© {{ date('Y') }} CampusService — Marketplace Layanan Mahasiswa</p>
            </div>
        </div>
    </footer>
</body>
</html>
