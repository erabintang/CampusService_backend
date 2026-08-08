@extends('layouts.public')

@section('content')
    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-violet-400/20 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <span class="inline-flex items-center rounded-full bg-white/10 px-4 py-1.5 text-xs font-medium text-indigo-100 ring-1 ring-white/20">
                    Marketplace Layanan Mahasiswa
                </span>
                <h1 class="mt-6 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    Butuh Bantuan untuk Tugas &amp; Proyekmu?
                </h1>
                <p class="mt-4 text-lg text-indigo-100">
                    Temukan layanan konsultasi, desain, editing, dan bantuan teknis dari mahasiswa lain di kampusmu — dengan bantuan yang wajar dan jujur.
                </p>

                <form method="GET" action="{{ route('products.index') }}" class="mx-auto mt-8 flex max-w-xl gap-2 rounded-full bg-white p-1.5 shadow-xl">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari layanan, misal: desain presentasi..."
                           class="w-full rounded-full border-0 bg-transparent px-4 py-2.5 text-sm text-gray-800 outline-none placeholder:text-gray-400 focus:ring-0">
                    <button type="submit" class="shrink-0 rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Cari
                    </button>
                </form>

                <p class="mt-4 text-sm text-indigo-200">
                    {{ number_format($activeProductCount) }} layanan tersedia untuk kamu
                </p>
            </div>
        </div>
    </section>

    {{-- ===== KATEGORI ===== --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Jelajahi Kategori</h2>
                <p class="mt-1 text-sm text-gray-500">Pilih kategori yang sesuai kebutuhanmu.</p>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            @forelse ($categories as $category)
                <a href="{{ route('products.index', ['category_id' => $category->id]) }}"
                   class="group flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-3.5 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-sm font-bold text-indigo-600">
                        {{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-gray-800 group-hover:text-indigo-600">{{ $category->name }}</span>
                        <span class="block text-xs text-gray-400">{{ $category->products_count }} layanan</span>
                    </span>
                </a>
            @empty
                <p class="text-sm text-gray-400">Belum ada kategori.</p>
            @endforelse
        </div>
    </section>

    {{-- ===== LAYANAN TERBARU ===== --}}
    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Layanan Terbaru</h2>
                <p class="mt-1 text-sm text-gray-500">Layanan yang baru ditambahkan oleh penyedia.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-700">
                Lihat Semua →
            </a>
        </div>

        @if ($latestProducts->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center">
                <p class="text-gray-400">Belum ada layanan tersedia. Silakan kembali lagi nanti.</p>
            </div>
        @else
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($latestProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- ===== CARA KERJA ===== --}}
    <section class="border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-center text-2xl font-bold text-gray-900">Cara Kerja</h2>
            <p class="mt-2 text-center text-sm text-gray-500">Tiga langkah mudah memesan layanan.</p>

            <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-3">
                @php
                    $steps = [
                        ['no' => '1', 'title' => 'Pilih Layanan', 'desc' => 'Jelajahi katalog dan pilih layanan yang kamu butuhkan.', 'icon' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z'],
                        ['no' => '2', 'title' => 'Pesan', 'desc' => 'Klik pesan dan dapatkan kode pesanan otomatis.', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'],
                        ['no' => '3', 'title' => 'Hubungi Penyedia', 'desc' => 'Diarahkan ke WhatsApp penyedia untuk diskusi dan pembayaran.', 'icon' => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z'],
                    ];
                @endphp
                @foreach ($steps as $step)
                    <div class="text-center">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}" /></svg>
                        </span>
                        <div class="mt-4 flex items-center justify-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">{{ $step['no'] }}</span>
                            <h3 class="font-semibold text-gray-900">{{ $step['title'] }}</h3>
                        </div>
                        <p class="mx-auto mt-2 max-w-xs text-sm text-gray-500">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== CTA ===== --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-12 text-center shadow-xl sm:px-12">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Siap Membantu atau Dibantu?</h2>
            <p class="mx-auto mt-3 max-w-xl text-indigo-100">
                Mau menawarkan keahlianmu atau mencari bantuan? CampusService menghubungkan keduanya.
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('products.index') }}" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-indigo-700 shadow transition hover:bg-indigo-50">Jelajahi Layanan</a>
                @guest
                    <a href="{{ route('register') }}" class="rounded-full bg-indigo-900/50 px-6 py-3 text-sm font-semibold text-white ring-1 ring-white/30 transition hover:bg-indigo-900/70">Daftar Sekarang</a>
                @endguest
            </div>
        </div>
    </section>
@endsection
