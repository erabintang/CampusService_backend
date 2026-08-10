<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Admin') }} — CampusService Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #sidebar.open { transform: translateX(0); }
    </style>
</head>
<body class="h-full bg-gray-100 text-gray-800 antialiased">
    {{-- Overlay untuk mobile --}}
    <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-gray-900/60" onclick="toggleSidebar()"></div>

    {{-- ===== SIDEBAR ===== --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 shadow-xl transition-transform duration-200 md:translate-x-0">
        {{-- Brand --}}
        <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-800 px-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">CS</span>
                <span class="leading-tight">
                    <span class="block text-sm font-semibold text-white">CampusService</span>
                    <span class="block text-[11px] font-normal text-slate-400">Admin Panel</span>
                </span>
            </a>
            <button class="text-slate-400 hover:text-white md:hidden" onclick="toggleSidebar()" aria-label="Tutup menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        {{-- Navigasi --}}
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @php
                $menu = [
                    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
                    ['label' => 'Produk', 'route' => 'admin.products.index', 'icon' => 'product'],
                    ['label' => 'Kategori', 'route' => 'admin.categories.index', 'icon' => 'category'],
                    ['label' => 'User', 'route' => 'admin.users.index', 'icon' => 'users'],
                    ['label' => 'Pesanan', 'route' => 'admin.orders.index', 'icon' => 'orders'],
                    ['label' => 'File Upload', 'route' => 'admin.uploads.index', 'icon' => 'reports'],
                    ['label' => 'Stok', 'route' => 'admin.products.index', 'icon' => 'stock'],
                    ['label' => 'Laporan', 'route' => 'admin.reports.index', 'icon' => 'reports'],
                ];
            @endphp
            @foreach ($menu as $item)
                @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
                @if (Route::has($item['route']))
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $active ? 'bg-indigo-600 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <x-admin-icon :name="$item['icon']" />
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-500" title="Fitur tersedia di tahap berikutnya">
                        <x-admin-icon :name="$item['icon']" class="opacity-60" />
                        {{ $item['label'] }}
                        <span class="ml-auto rounded bg-slate-800 px-1.5 py-0.5 text-[10px] uppercase tracking-wider text-slate-400">segera</span>
                    </span>
                @endif
            @endforeach
        </nav>

        {{-- Footer --}}
        <div class="shrink-0 space-y-1 border-t border-slate-800 p-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-white">
                <x-admin-icon name="home" />
                Lihat Website
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-white">
                <x-admin-icon name="user" />
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-rose-300 transition-colors hover:bg-rose-900/40 hover:text-rose-200">
                    <x-admin-icon name="logout" />
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ===== KONTEN ===== --}}
    <div class="flex min-h-screen flex-col md:pl-64">
        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center justify-between border-b border-gray-200 bg-white/95 px-4 backdrop-blur md:px-8">
            <div class="flex items-center gap-3">
                <button class="text-gray-600 hover:text-gray-900 md:hidden" onclick="toggleSidebar()" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden text-sm font-medium text-gray-700 sm:block">{{ auth()->user()->name }}</span>
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">Admin</span>
            </div>
        </header>

        {{-- Konten utama --}}
        <main class="flex-1 p-4 md:p-8">
            @if (session('success'))
                <div class="mb-4 flex items-center gap-2 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 flex items-center gap-2 rounded-lg bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 ring-1 ring-rose-200">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="shrink-0 px-4 pb-4 text-center text-xs text-gray-400 md:px-8">
            CampusService Admin — Laravel {{ Illuminate\Foundation\Application::VERSION }}
        </footer>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('hidden');
        }

        // Konfirmasi hapus: form dengan atribut data-confirm
        document.addEventListener('submit', function (event) {
            const message = event.target.getAttribute('data-confirm');
            if (message && !confirm(message)) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>
