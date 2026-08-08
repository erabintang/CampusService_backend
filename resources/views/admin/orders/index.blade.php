@php $title = 'Pesanan'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        {{-- Header --}}
        <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Manajemen Pesanan</h2>
                <p class="text-sm text-gray-500">Pantau dan kelola status pesanan layanan.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                {{ number_format($orders->total()) }} pesanan
            </span>
        </div>

        {{-- Filter --}}
        <div class="border-b border-gray-100 px-6 py-4">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode pesanan atau user..." class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 lg:max-w-xs">
                <select name="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    @foreach (['pending', 'processing', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">Filter</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
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
                        <th class="px-6 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($orders as $order)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-xs font-medium text-indigo-600">{{ $order->order_code }}</td>
                            <td class="px-6 py-3">
                                <p class="font-medium text-gray-900">{{ $order->user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $order->user->email }}</p>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $order->product->name }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">Rp{{ number_format((float) $order->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-3">
                                <x-order-status :status="$order->status" />
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 ring-1 ring-indigo-100 transition hover:bg-indigo-100">Detail</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                {{ request()->hasAny(['search', 'status']) ? 'Tidak ada pesanan yang cocok dengan filter.' : 'Belum ada pesanan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($orders->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
