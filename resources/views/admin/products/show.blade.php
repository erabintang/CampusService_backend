@php $title = 'Detail Produk'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-gray-900">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke Produk
            </a>
            <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">Edit Produk</a>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-64 w-full object-cover">
            @else
                <div class="flex h-40 items-center justify-center bg-indigo-50">
                    <span class="text-4xl font-bold text-indigo-300">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                </div>
            @endif

            <div class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-600 ring-1 ring-indigo-100">{{ $product->category->name }}</span>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $product->status ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-gray-100 text-gray-500 ring-gray-200' }}">
                                {{ $product->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            @if ($product->stock == 0)
                                <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-600 ring-1 ring-rose-200">Slot penuh</span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $product->stock }} slot tersedia</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-indigo-600">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</p>
                </div>

                <dl class="mt-6 space-y-4 border-t border-gray-100 pt-6 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500">Deskripsi</dt>
                        <dd class="mt-1 whitespace-pre-line text-gray-800">{{ $product->description }}</dd>
                    </div>
                    @if ($product->included)
                        <div>
                            <dt class="font-medium text-gray-500">Yang Didapat</dt>
                            <dd class="mt-1 whitespace-pre-line text-gray-800">{{ $product->included }}</dd>
                        </div>
                    @endif
                    @if ($product->payment_info)
                        <div>
                            <dt class="font-medium text-gray-500">Informasi Pembayaran</dt>
                            <dd class="mt-1 whitespace-pre-line text-gray-800">{{ $product->payment_info }}</dd>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="font-medium text-gray-500">Estimasi</dt>
                            <dd class="mt-0.5 text-gray-800">{{ $product->duration ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">WhatsApp</dt>
                            <dd class="mt-0.5 text-gray-800">{{ $product->whatsapp }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Slug</dt>
                            <dd class="mt-0.5 font-mono text-xs text-gray-500">{{ $product->slug }}</dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
