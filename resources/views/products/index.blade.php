@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="max-w-2xl">
            <h1 class="text-3xl font-extrabold text-gray-900">Daftar Layanan</h1>
            <p class="mt-2 text-gray-500">Semua layanan aktif yang tersedia dari mahasiswa untuk mahasiswa.</p>
        </div>

        {{-- Search + Filter --}}
        <div class="mt-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama layanan..."
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <select name="category_id" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-56">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">Cari</button>
                    @if (request()->hasAny(['search', 'category_id']))
                        <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Kategori chips --}}
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('products.index') }}"
               class="rounded-full px-4 py-1.5 text-xs font-medium transition {{ ! request('category_id') ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                Semua
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category_id' => $category->id] + request()->only('search')) }}"
                   class="rounded-full px-4 py-1.5 text-xs font-medium transition {{ request('category_id') == $category->id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        {{-- Grid produk --}}
        @if ($products->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white p-14 text-center">
                <p class="text-3xl">🔍</p>
                <p class="mt-3 font-medium text-gray-600">Tidak ada layanan yang cocok</p>
                <p class="mt-1 text-sm text-gray-400">Coba ubah kata kunci pencarian atau pilih kategori lain.</p>
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            @if ($products->hasPages())
                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
