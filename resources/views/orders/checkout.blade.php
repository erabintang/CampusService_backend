@extends('layouts.public')

@php
    $soldOut = $product->stock < 1;
    $formattedPrice = 'Rp'.number_format((float) $product->price, 0, ',', '.');
@endphp

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        {{-- ===== Breadcrumb ===== --}}
        <nav class="flex flex-wrap items-center gap-1.5 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="transition hover:text-indigo-600">Beranda</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('products.index') }}" class="transition hover:text-indigo-600">Layanan</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('products.show', $product) }}" class="transition hover:text-indigo-600">{{ $product->name }}</a>
            <span class="text-gray-300">/</span>
            <span class="font-medium text-gray-800">Checkout</span>
        </nav>

        <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-5">
            {{-- ===== Detail produk ===== --}}
            <div class="lg:col-span-3">
                <div class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-gray-200">
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="aspect-[16/7] w-full object-cover">
                    @else
                        <div class="flex aspect-[16/7] w-full items-center justify-center bg-gradient-to-br from-indigo-50 to-violet-100">
                            <span class="text-8xl font-bold text-indigo-200">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                        </div>
                    @endif

                    <div class="p-6 sm:p-8">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600 ring-1 ring-indigo-100">{{ $product->category->name }}</span>
                            @if ($soldOut)
                                <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-600 ring-1 ring-rose-100">Slot Penuh</span>
                            @else
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-100">{{ $product->stock }} slot tersedia</span>
                            @endif
                        </div>

                        <h1 class="mt-3 text-2xl font-extrabold text-gray-900 sm:text-3xl">{{ $product->name }}</h1>

                        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $product->description }}</p>

                        @if ($product->included)
                            <div class="mt-6">
                                <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Yang Kamu Dapat</h2>
                                <ul class="mt-2 space-y-1.5">
                                    @foreach (preg_split('/\r\n|\r|\n/', $product->included) as $item)
                                        @if (trim($item))
                                            <li class="flex items-start gap-2 text-sm text-gray-700">
                                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                {{ trim($item) }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===== Ringkasan pesanan ===== --}}
            <div class="lg:col-span-2">
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:p-7">
                    <h2 class="text-lg font-bold text-gray-900">Ringkasan Pesanan</h2>

                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500">Pemesan</dt>
                            <dd class="text-right font-medium text-gray-800">
                                {{ auth()->user()->name }}<br>
                                <span class="text-xs font-normal text-gray-400">{{ auth()->user()->email }}</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Layanan</dt>
                            <dd class="text-right font-medium text-gray-800">{{ $product->name }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Harga</dt>
                            <dd class="text-right text-base font-extrabold text-indigo-600">{{ $formattedPrice }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Kode pesanan</dt>
                            <dd class="text-right font-medium text-gray-800">Dibuat otomatis</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Status</dt>
                            <dd class="text-right"><span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-100">Pending</span></dd>
                        </div>
                    </dl>

                    @if ($soldOut)
                        <div class="mt-6 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-100">
                            Slot layanan ini sudah penuh, jadi tidak bisa dipesan.
                        </div>
                        <a href="{{ route('products.show', $product) }}"
                           class="mt-4 flex w-full items-center justify-center rounded-xl bg-gray-800 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-gray-700">
                            Kembali ke Layanan
                        </a>
                    @else
                        <form id="checkout-form" method="POST" action="{{ route('checkout.store', $product) }}" class="mt-6">
                            @csrf
                            <button type="submit"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/20 transition hover:bg-indigo-700 hover:shadow-lg">
                                Konfirmasi &amp; Lanjut ke WhatsApp
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </button>
                        </form>

                        <div class="mt-4 flex items-start gap-2 rounded-xl bg-indigo-50/70 px-4 py-3 text-xs leading-relaxed text-indigo-900 ring-1 ring-indigo-100">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                            <span>Setelah konfirmasi, pesanan dibuat dan kamu diarahkan ke <strong>WhatsApp penyedia</strong> dengan pesan otomatis berisi kode pesananmu. Komunikasi &amp; pembayaran dilakukan langsung dengan penyedia.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cegah submit ganda (double order) — tombol dinonaktifkan saat diklik.
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (form.id !== 'checkout-form') return;
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                var original = btn.textContent;
                btn.disabled = true;
                btn.textContent = 'Memproses...';
                // Aktifkan kembali jika respons gagal/tertunda (mis. sesi CSRF kedaluwarsa).
                setTimeout(function () {
                    btn.disabled = false;
                    btn.textContent = original;
                }, 10000);
            }
        });
    </script>
@endsection
