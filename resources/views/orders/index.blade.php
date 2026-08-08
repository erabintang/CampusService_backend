@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Riwayat Pesanan</h1>
                <p class="mt-1 text-sm text-gray-500">Semua layanan yang pernah kamu pesan di CampusService.</p>
            </div>
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/20 transition hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Pesan Layanan Baru
            </a>
        </div>

        {{-- Search + filter --}}
        <form method="GET" action="{{ route('orders.index') }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode pesanan, misal ORD-20260808-0001..."
                       class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            </div>
            <select name="status" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                <option value="">Semua Status</option>
                @foreach (\App\Models\Order::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-gray-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700">
                Cari
            </button>
            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route('orders.index') }}" class="inline-flex items-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Reset</a>
            @endif
        </form>

        {{-- Daftar pesanan --}}
        <div class="mt-6 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-200">
            @if ($orders->isEmpty())
                <div class="p-16 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-400">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" /></svg>
                    </span>
                    <p class="mt-4 font-semibold text-gray-800">Belum ada pesanan</p>
                    <p class="mt-1 text-sm text-gray-500">Jelajahi katalog layanan dan buat pesanan pertamamu.</p>
                    <a href="{{ route('products.index') }}" class="mt-4 inline-block text-sm font-semibold text-indigo-600 transition hover:text-indigo-700">Jelajahi Layanan →</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                        <thead class="bg-gray-50/70 text-xs uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-6 py-3.5 font-medium">Kode Pesanan</th>
                                <th class="px-6 py-3.5 font-medium">Layanan</th>
                                <th class="px-6 py-3.5 font-medium">Harga</th>
                                <th class="px-6 py-3.5 font-medium">Status</th>
                                <th class="px-6 py-3.5 font-medium">Tanggal</th>
                                <th class="px-6 py-3.5 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($orders as $order)
                                <tr class="transition hover:bg-indigo-50/40">
                                    <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-800">{{ $order->order_code }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($order->product->image)
                                                <img src="{{ asset('storage/' . $order->product->image) }}" alt="" class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-100">
                                            @else
                                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-500">{{ mb_strtoupper(mb_substr($order->product->name, 0, 1)) }}</span>
                                            @endif
                                            <div>
                                                <p class="font-medium text-gray-800">{{ $order->product->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $order->product->category->name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-800">Rp{{ number_format((float) $order->price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4"><x-order-status :status="$order->status" /></td>
                                    <td class="px-6 py-4 text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center gap-1 font-medium text-indigo-600 transition hover:text-indigo-700">
                                            Detail
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
