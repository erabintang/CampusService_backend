@php $title = 'Dashboard'; @endphp
@extends('layouts.admin')

@section('content')
    {{-- Kartu statistik --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @php
            $cards = [
                ['label' => 'Total Produk', 'value' => number_format($totalProducts), 'icon' => 'product', 'accent' => 'bg-blue-50 text-blue-600 ring-blue-100'],
                ['label' => 'Total Kategori', 'value' => number_format($totalCategories), 'icon' => 'category', 'accent' => 'bg-indigo-50 text-indigo-600 ring-indigo-100'],
                ['label' => 'Total User', 'value' => number_format($totalUsers), 'icon' => 'users', 'accent' => 'bg-emerald-50 text-emerald-600 ring-emerald-100'],
                ['label' => 'Total Pesanan', 'value' => number_format($totalOrders), 'icon' => 'orders', 'accent' => 'bg-amber-50 text-amber-600 ring-amber-100'],
                ['label' => 'Pendapatan', 'value' => 'Rp' . number_format($totalRevenue, 0, ',', '.'), 'icon' => 'reports', 'accent' => 'bg-violet-50 text-violet-600 ring-violet-100'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition-shadow hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                        <p class="mt-1.5 text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                    </div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl ring-1 {{ $card['accent'] }}">
                        <x-admin-icon :name="$card['icon']" class="h-6 w-6" />
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Grafik 7 hari + status pesanan --}}
    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-gray-800">Pesanan 7 Hari Terakhir</h2>
                <span class="text-xs text-gray-400">jumlah pesanan per hari</span>
            </div>
            @php $chartMax = max($dailyOrders->max(), 1); @endphp
            <div class="flex h-44 items-end gap-2 px-4 pb-4 pt-6 sm:gap-3 sm:px-6" role="img" aria-label="Grafik pesanan 7 hari terakhir">
                @foreach ($dailyOrders as $date => $count)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-2" title="{{ $date }}: {{ $count }} pesanan">
                        <span class="text-xs font-semibold {{ $count > 0 ? 'text-gray-700' : 'text-gray-300' }}">{{ $count }}</span>
                        <div class="flex w-full max-w-[42px] flex-1 items-end rounded-lg bg-gray-100">
                            <div class="w-full rounded-lg bg-gradient-to-t from-indigo-600 to-indigo-400 transition-all duration-500"
                                 style="height: {{ $count > 0 ? max(10, round($count / $chartMax * 100)) : 4 }}%"></div>
                        </div>
                        <span class="text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-gray-800">Status Pesanan</h2>
            </div>
            <div class="space-y-3 px-6 py-4">
                @php
                    $statusList = [
                        ['key' => 'pending', 'label' => 'Pending', 'color' => 'text-amber-600', 'bg' => 'bg-amber-100'],
                        ['key' => 'processing', 'label' => 'Processing', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100'],
                        ['key' => 'completed', 'label' => 'Completed', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-100'],
                        ['key' => 'cancelled', 'label' => 'Cancelled', 'color' => 'text-rose-600', 'bg' => 'bg-rose-100'],
                    ];
                @endphp
                @foreach ($statusList as $s)
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="h-2.5 w-2.5 rounded-full {{ $s['bg'] }}"></span>
                            {{ $s['label'] }}
                        </span>
                        <span class="text-sm font-semibold {{ $s['color'] }}">{{ number_format($statusCounts[$s['key']]) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Pendapatan per status + layanan terlaris + stok menipis --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-gray-800">Pendapatan per Status</h2>
            </div>
            <div class="space-y-3 px-6 py-4">
                @foreach ($statusList as $s)
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="h-2.5 w-2.5 rounded-full {{ $s['bg'] }}"></span>
                            {{ $s['label'] }}
                        </span>
                        <span class="text-sm font-semibold text-gray-800">Rp{{ number_format((float) $revenueByStatus[$s['key']], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-3">
                    <span class="text-sm font-medium text-gray-500">Total (non-batal)</span>
                    <span class="text-base font-bold text-indigo-600">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-gray-800">Layanan Terlaris</h2>
            </div>
            @if ($topProducts->isEmpty() || $topProducts->sum('orders_count') === 0)
                <p class="px-6 py-6 text-sm text-gray-400">Belum ada pesanan.</p>
            @else
                <ul class="divide-y divide-gray-50 px-6">
                    @foreach ($topProducts as $index => $product)
                        <li class="flex items-center gap-3 py-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">{{ $index + 1 }}</span>
                            <span class="min-w-0 flex-1 truncate text-sm text-gray-700">{{ $product->name }}</span>
                            <span class="shrink-0 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-600 ring-1 ring-indigo-100">{{ $product->orders_count }}×</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="font-semibold text-gray-800">Stok Menipis</h2>
            </div>
            @if ($lowStockProducts->isEmpty())
                <p class="px-6 py-6 text-sm text-gray-400">Semua stok aman.</p>
            @else
                <ul class="divide-y divide-gray-50 px-6">
                    @foreach ($lowStockProducts as $product)
                        <li class="flex items-center justify-between py-3">
                            <span class="truncate pr-3 text-sm text-gray-700">{{ $product->name }}</span>
                            <span class="shrink-0 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-200">
                                {{ $product->stock }} slot
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Pesanan terbaru --}}
    <div class="mt-6 rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <h2 class="font-semibold text-gray-800">Pesanan Terbaru</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-medium text-indigo-600 transition hover:text-indigo-700">Lihat Semua →</a>
        </div>
        @if ($latestOrders->isEmpty())
            <p class="px-6 py-10 text-center text-sm text-gray-400">Belum ada pesanan.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-6 py-3 font-medium">Kode</th>
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium">Produk</th>
                            <th class="px-6 py-3 font-medium">Harga</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($latestOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-mono text-xs font-medium text-indigo-600">{{ $order->order_code }}</td>
                                <td class="px-6 py-3 text-gray-700">{{ $order->user->name }}</td>
                                <td class="px-6 py-3 text-gray-700">{{ $order->product->name }}</td>
                                <td class="px-6 py-3 text-gray-900">Rp{{ number_format((float) $order->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3">
                                    <x-order-status :status="$order->status" />
                                </td>
                                <td class="px-6 py-3 text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
