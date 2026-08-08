@extends('layouts.public')

@php
    $soldOut = $product->stock < 1;
    // PHASE 13: tombol disiapkan — otomatis menjadi link checkout saat route dibuat di PHASE 14.
    $checkoutReady = Route::has('checkout') && ! $soldOut;
    $soldText = $soldCount > 0 ? number_format($soldCount) . '× dipesan' : 'Baru di CampusService';
    $steps = [
        ['no' => '1', 'title' => 'Pesan Sekarang', 'desc' => 'Klik tombol pesan, sistem membuat kode pesanan dan mengurangi slot secara otomatis.', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'],
        ['no' => '2', 'title' => 'Hubungi Penyedia', 'desc' => 'Diarahkan ke WhatsApp penyedia dengan pesan otomatis berisi kode pesananmu.', 'icon' => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z'],
        ['no' => '3', 'title' => 'Diskusi & Pembayaran', 'desc' => 'Diskusikan detail kebutuhan, sepakati hasil, dan selesaikan pembayaran langsung dengan penyedia.', 'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z'],
    ];
@endphp

@section('content')
    <div class="mx-auto max-w-6xl px-4 pb-32 pt-8 sm:px-6 lg:px-8 lg:pb-14">
        {{-- ===== Breadcrumb ===== --}}
        <nav class="flex flex-wrap items-center gap-1.5 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="transition hover:text-indigo-600">Beranda</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('products.index') }}" class="transition hover:text-indigo-600">Layanan</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('products.index', ['category_id' => $product->category_id]) }}" class="transition hover:text-indigo-600">{{ $product->category->name }}</a>
            <span class="text-gray-300">/</span>
            <span class="font-medium text-gray-800">{{ $product->name }}</span>
        </nav>

        <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-3">
            {{-- ===== KOLOM UTAMA ===== --}}
            <div class="lg:col-span-2">
                {{-- Gambar dengan badge overlay --}}
                <div class="group relative overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-200">
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                             class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                    @else
                        <div class="flex aspect-[4/3] w-full items-center justify-center bg-gradient-to-br from-indigo-50 to-violet-100">
                            <span class="text-9xl font-bold text-indigo-200">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                        </div>
                    @endif

                    <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                        <a href="{{ route('products.index', ['category_id' => $product->category_id]) }}"
                           class="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-indigo-600 shadow-sm ring-1 ring-indigo-100 backdrop-blur transition hover:bg-white">
                            {{ $product->category->name }}
                        </a>
                    </div>
                    @if ($soldOut)
                        <span class="absolute right-4 top-4 rounded-full bg-rose-600/95 px-3 py-1 text-xs font-semibold text-white shadow-sm backdrop-blur">Slot Penuh</span>
                    @endif
                </div>

                {{-- Judul + meta --}}
                <div class="mt-6">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($soldCount > 0)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-100">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                                {{ $soldText }}
                            </span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500 ring-1 ring-gray-200">Baru di CampusService</span>
                        @endif
                        @if ($soldOut)
                            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-600 ring-1 ring-rose-100">Slot Penuh</span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-100">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                                {{ $product->stock }} slot tersedia
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-3 text-3xl font-extrabold leading-tight text-gray-900 sm:text-4xl">{{ $product->name }}</h1>

                    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500">
                        @if ($product->duration)
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Estimasi {{ $product->duration }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                Penyedia Mahasiswa
                            </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Bantuan wajar &amp; jujur
                        </span>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <p class="mt-6 whitespace-pre-line leading-relaxed text-gray-600">{{ $product->description }}</p>

                {{-- Yang Kamu Dapat --}}
                @if ($product->included)
                    <div class="mt-8">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Yang Kamu Dapat</h2>
                        <ul class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach (preg_split('/\r\n|\r|\n/', $product->included) as $item)
                                @if (trim($item))
                                    <li class="flex items-start gap-2 text-sm text-gray-700">
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        </span>
                                        {{ trim($item) }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Informasi Pembayaran --}}
                @if ($product->payment_info)
                    <div class="mt-8 rounded-2xl bg-amber-50 px-5 py-4 text-sm text-amber-900 ring-1 ring-amber-100">
                        <p class="flex items-center gap-2 font-semibold">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                            Informasi Pembayaran
                        </p>
                        <p class="mt-1.5 leading-relaxed">{{ $product->payment_info }}</p>
                    </div>
                @endif

                {{-- Cara Pemesanan --}}
                <div class="mt-10 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:p-8">
                    <h2 class="text-xl font-bold text-gray-900">Cara Pemesanan</h2>
                    <p class="mt-1 text-sm text-gray-500">Tiga langkah mudah memesan layanan ini.</p>

                    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-3">
                        @foreach ($steps as $step)
                            <div class="relative">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}" /></svg>
                                </span>
                                <div class="mt-3 flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-bold text-white">{{ $step['no'] }}</span>
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $step['title'] }}</h3>
                                </div>
                                <p class="mt-1.5 text-xs leading-relaxed text-gray-500">{{ $step['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="mt-6 rounded-2xl bg-indigo-50/70 px-5 py-4 text-sm text-indigo-900 ring-1 ring-indigo-100">
                    <p class="flex items-center gap-2 font-semibold">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                        Catatan
                    </p>
                    <p class="mt-1.5 leading-relaxed">
                        Layanan ini merupakan bentuk bantuan akademik yang wajar — konsultasi, tutoring, proofreading, desain, bantuan teknis, atau dokumentasi. Semua komunikasi dan pembayaran dilakukan langsung dengan penyedia melalui WhatsApp.
                    </p>
                </div>
            </div>

            {{-- ===== KARTU RINGKASAN (sticky) ===== --}}
            <aside class="lg:sticky lg:top-24 lg:h-fit">
                {{-- Kartu harga + CTA hanya di desktop; di mobile digantikan sticky bottom bar --}}
                <div class="hidden rounded-3xl bg-white p-6 shadow-sm ring-1 ring-gray-200 lg:block">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Harga Layanan</p>
                    <p class="mt-1 text-3xl font-extrabold text-indigo-600">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</p>

                    <div class="mt-4">
                        @if ($soldOut)
                            <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1.5 text-sm font-semibold text-rose-600 ring-1 ring-rose-100">Slot Penuh</span>
                        @else
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                                {{ $product->stock }} slot tersedia
                            </span>
                        @endif
                    </div>

                    <div class="mt-5">
                        @if ($checkoutReady)
                            <a href="{{ route('checkout', $product->slug) }}"
                               class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/20 transition hover:bg-indigo-700 hover:shadow-lg">
                                Pesan Sekarang
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        @else
                            {{-- PHASE 14: tombol otomatis menjadi link checkout saat route dibuat --}}
                            <button type="button" disabled
                                    title="{{ $soldOut ? 'Slot sudah penuh' : 'Checkout akan tersedia di tahap berikutnya' }}"
                                    class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-gray-200 px-6 py-3.5 text-sm font-semibold text-gray-500">
                                {{ $soldOut ? 'Slot Penuh' : 'Pesan Sekarang' }}
                            </button>
                        @endif
                    </div>

                    <dl class="mt-6 space-y-3 border-t border-gray-100 pt-5 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">Estimasi</dt>
                            <dd class="font-medium text-gray-800">{{ $product->duration ?? 'Menyesuaikan' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">Kategori</dt>
                            <dd class="font-medium text-gray-800">{{ $product->category->name }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">Terjual</dt>
                            <dd class="font-medium text-gray-800">{{ $soldCount }} pesanan</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">Slot tersisa</dt>
                            <dd class="font-medium text-gray-800">{{ $product->stock }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Kartu penyedia --}}
                <div class="mt-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white">CS</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Penyedia Layanan</p>
                            <p class="text-xs text-gray-500">Mahasiswa CampusService · Terverifikasi</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-gray-500">
                        Komunikasi dilakukan langsung dengan penyedia via WhatsApp setelah kamu memesan.
                    </p>
                </div>

                {{-- Trust --}}
                <div class="mt-4 rounded-2xl bg-indigo-50/60 p-5 ring-1 ring-indigo-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">Kenapa CampusService?</p>
                    <ul class="mt-3 space-y-1.5 text-xs text-indigo-900">
                        <li class="flex items-start gap-1.5"><span class="font-bold text-indigo-600">✓</span> Bantuan akademik yang wajar &amp; jujur</li>
                        <li class="flex items-start gap-1.5"><span class="font-bold text-indigo-600">✓</span> Harga jelas, tanpa biaya tersembunyi</li>
                        <li class="flex items-start gap-1.5"><span class="font-bold text-indigo-600">✓</span> Slot terbatas — kualitas terjaga</li>
                    </ul>
                </div>
            </aside>
        </div>

        {{-- ===== LAYANAN SERUPA ===== --}}
        @if ($relatedProducts->isNotEmpty())
            <section class="mt-16">
                <div class="flex items-end justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Layanan Serupa</h2>
                        <p class="mt-1 text-sm text-gray-500">Layanan lain dari kategori {{ $product->category->name }}.</p>
                    </div>
                    <a href="{{ route('products.index', ['category_id' => $product->category_id]) }}" class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-700">
                        Lihat Semua →
                    </a>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- ===== MOBILE STICKY BAR ===== --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 px-4 py-3 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] backdrop-blur lg:hidden">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-400">Harga Layanan</p>
                <p class="truncate text-lg font-extrabold text-indigo-600">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</p>
            </div>
            @if ($checkoutReady)
                <a href="{{ route('checkout', $product->slug) }}"
                   class="shrink-0 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-md shadow-indigo-600/20 transition hover:bg-indigo-700">
                    Pesan Sekarang
                </a>
            @else
                <button type="button" disabled
                        title="{{ $soldOut ? 'Slot sudah penuh' : 'Checkout akan tersedia di tahap berikutnya' }}"
                        class="shrink-0 cursor-not-allowed rounded-xl bg-gray-200 px-5 py-3 text-sm font-semibold text-gray-500">
                    {{ $soldOut ? 'Slot Penuh' : 'Pesan Sekarang' }}
                </button>
            @endif
        </div>
    </div>
@endsection
