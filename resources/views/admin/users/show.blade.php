@php $title = 'Detail User'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-gray-900">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke User
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">Edit User</a>
        </div>

        {{-- Profil user --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-6 sm:flex-row sm:items-center">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full {{ $user->isAdmin() ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500' }} text-2xl font-bold">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </span>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $user->isAdmin() ? 'bg-indigo-50 text-indigo-600 ring-indigo-200' : 'bg-gray-100 text-gray-600 ring-gray-200' }}">{{ $user->role }}</span>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $user->status ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-600 ring-rose-200' }}">
                            {{ $user->status ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Terdaftar {{ $user->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>

            <dl class="grid grid-cols-1 gap-4 px-6 py-5 text-sm sm:grid-cols-3">
                <div>
                    <dt class="font-medium text-gray-500">Email</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Nomor HP</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $user->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Jumlah Pesanan</dt>
                    <dd class="mt-0.5 text-gray-800">{{ number_format($orders->total()) }} pesanan</dd>
                </div>
            </dl>
        </div>

        {{-- Riwayat pesanan --}}
        <div class="mt-6 rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="font-semibold text-gray-800">Riwayat Pesanan</h3>
            </div>
            @if ($orders->isEmpty())
                <p class="px-6 py-10 text-center text-sm text-gray-400">User ini belum memiliki pesanan.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-400">
                                <th class="px-6 py-3 font-medium">Kode</th>
                                <th class="px-6 py-3 font-medium">Produk</th>
                                <th class="px-6 py-3 font-medium">Harga</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs font-medium text-indigo-600">{{ $order->order_code }}</td>
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
                @if ($orders->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
