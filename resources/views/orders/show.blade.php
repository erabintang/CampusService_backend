@extends('layouts.public')

@php
    $statuses = array_slice(\App\Models\Order::STATUSES, 0, 3);
    $currentIndex = array_search($order->status, $statuses, true);
    $currentIndex = $currentIndex === false ? null : $currentIndex;
    $isCancelled = $order->status === 'cancelled';
@endphp

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="flex flex-wrap items-center gap-1.5 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="transition hover:text-indigo-600">Beranda</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('orders.index') }}" class="transition hover:text-indigo-600">Riwayat Pesanan</a>
            <span class="text-gray-300">/</span>
            <span class="font-mono font-medium text-gray-800">{{ $order->order_code }}</span>
        </nav>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-mono text-2xl font-extrabold text-gray-900 sm:text-3xl">{{ $order->order_code }}</h1>
                <p class="mt-1 text-sm text-gray-500">Dipesan pada {{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            <x-order-status :status="$order->status" />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-5">
            {{-- Info pesanan --}}
            <div class="lg:col-span-3">
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:p-8">
                    <h2 class="text-lg font-bold text-gray-900">Informasi Pesanan</h2>

                    <dl class="mt-5 grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Pemesan</dt>
                            <dd class="mt-1 font-medium text-gray-800">{{ $order->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Email</dt>
                            <dd class="mt-1 text-gray-600">{{ $order->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Layanan</dt>
                            <dd class="mt-1 font-medium text-gray-800">{{ $order->product->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Kategori</dt>
                            <dd class="mt-1 text-gray-600">{{ $order->product->category->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Harga</dt>
                            <dd class="mt-1 text-base font-extrabold text-indigo-600">Rp{{ number_format((float) $order->price, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Status</dt>
                            <dd class="mt-1"><x-order-status :status="$order->status" /></dd>
                        </div>
                    </dl>

                    {{-- Timeline status --}}
                    @if ($isCancelled)
                        <div class="mt-8 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-100">
                            Pesanan ini telah <strong>dibatalkan</strong>. Slot layanan sudah dikembalikan. Kamu bisa memesan kembali layanan lain kapan saja.
                        </div>
                    @else
                        <div class="mt-8">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Perkembangan Pesanan</h3>
                            <div class="mt-4 flex items-start gap-2">
                                @foreach ($statuses as $i => $status)
                                    <div class="flex flex-1 flex-col items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold ring-2 {{ $i <= $currentIndex ? 'bg-indigo-600 text-white ring-indigo-600' : 'bg-white text-gray-400 ring-gray-200' }}">
                                            {{ $i <= $currentIndex ? ($i < $currentIndex ? '✓' : $i + 1) : $i + 1 }}
                                        </span>
                                        <span class="text-xs font-medium {{ $i <= $currentIndex ? 'text-gray-800' : 'text-gray-400' }} capitalize">{{ $status }}</span>
                                    </div>
                                    @if (! $loop->last)
                                        <div class="mt-4 h-0.5 flex-1 rounded-full {{ $i < $currentIndex ? 'bg-indigo-500' : 'bg-gray-200' }}"></div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($order->product->payment_info)
                        <div class="mt-8 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-100">
                            <span class="font-semibold">Informasi Pembayaran:</span> {{ $order->product->payment_info }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Ringkasan produk + WhatsApp --}}
            <div class="lg:col-span-2">
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Layanan yang Dipesan</h2>

                    <div class="mt-4 overflow-hidden rounded-2xl ring-1 ring-gray-100">
                        @if ($order->product->image)
                            <img src="{{ asset('storage/' . $order->product->image) }}" alt="{{ $order->product->name }}" class="aspect-[4/3] w-full object-cover">
                        @else
                            <div class="flex aspect-[4/3] w-full items-center justify-center bg-gradient-to-br from-indigo-50 to-violet-100">
                                <span class="text-6xl font-bold text-indigo-200">{{ mb_strtoupper(mb_substr($order->product->name, 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $order->product->name }}</p>
                            <p class="text-xs text-gray-400">{{ $order->product->category->name }} · {{ $order->product->duration ?? 'Estimasi menyesuaikan' }}</p>
                        </div>
                        <span class="shrink-0 text-lg font-extrabold text-indigo-600">Rp{{ number_format((float) $order->price, 0, ',', '.') }}</span>
                    </div>

                    <a href="{{ route('products.show', $order->product) }}" class="mt-3 block text-sm font-semibold text-indigo-600 transition hover:text-indigo-700">
                        Lihat Detail Layanan →
                    </a>

                    <div class="mt-5 border-t border-gray-100 pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Butuh bantuan penyedia?</p>
                        <p class="mt-1 text-xs leading-relaxed text-gray-500">Hubungi penyedia langsung via WhatsApp dengan pesan otomatis berisi kode pesananmu.</p>
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                           class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-6 py-3.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/20 transition hover:bg-emerald-600">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Hubungi Penyedia via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
