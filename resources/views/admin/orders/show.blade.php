@php $title = 'Detail Pesanan'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-gray-900">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke Pesanan
            </a>
        </div>

        {{-- Ringkasan pesanan --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-5">
                <div>
                    <p class="font-mono text-sm font-semibold text-indigo-600">{{ $order->order_code }}</p>
                    <p class="text-xs text-gray-400">Dibuat {{ $order->created_at->format('d M Y, H:i') }}</p>
                </div>
                <x-order-status :status="$order->status" class="px-3 py-1 text-xs font-semibold" />
            </div>

            <dl class="grid grid-cols-1 gap-5 px-6 py-6 text-sm sm:grid-cols-2">
                <div class="rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">User</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $order->user->name }}</dd>
                    <dd class="mt-0.5 text-gray-500">{{ $order->user->email }}</dd>
                    <dd class="mt-0.5 text-gray-500">{{ $order->user->phone ?? 'Tanpa nomor HP' }}</dd>
                </div>

                <div class="rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Produk</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $order->product->name }}</dd>
                    <dd class="mt-0.5 text-gray-500">{{ $order->product->category->name }}</dd>
                    <dd class="mt-0.5 text-gray-500">Harga saat checkout: <span class="font-semibold text-gray-900">Rp{{ number_format((float) $order->price, 0, ',', '.') }}</span></dd>
                </div>
            </dl>

            {{-- Ubah status --}}
            <div class="border-t border-gray-100 px-6 py-5">
                <h3 class="text-sm font-semibold text-gray-800">Ubah Status Pesanan</h3>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (['pending', 'processing', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">Simpan Status</button>
                    @error('status')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </form>
                <p class="mt-2 text-xs text-gray-400">
                    @if ($order->status !== 'cancelled')
                        Membatalkan pesanan akan mengembalikan slot (stok) produk.
                    @else
                        Pesanan ini sudah dibatalkan; slot telah dikembalikan.
                    @endif
                </p>
            </div>
        </div>
    </div>
@endsection
