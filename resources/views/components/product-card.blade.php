@props(['product'])

<a href="{{ route('products.show', $product) }}" class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 transition duration-200 hover:-translate-y-0.5 hover:shadow-lg">
    @if ($product->image)
        <div class="aspect-[4/3] w-full overflow-hidden bg-gray-100">
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        </div>
    @else
        <div class="flex aspect-[4/3] w-full items-center justify-center bg-gradient-to-br from-indigo-50 to-violet-100">
            <span class="text-5xl font-bold text-indigo-300">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
        </div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center justify-between gap-2">
            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-600">{{ $product->category->name }}</span>
            @if ($product->stock == 0)
                <span class="rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-600">Slot penuh</span>
            @else
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ $product->stock }} slot</span>
            @endif
        </div>

        <h3 class="mt-3 line-clamp-2 font-semibold text-gray-900 group-hover:text-indigo-600">{{ $product->name }}</h3>

        <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ $product->description }}</p>

        <div class="mt-4 flex items-end justify-between border-t border-gray-100 pt-4">
            <span class="text-lg font-bold text-indigo-600">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</span>
            <span class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 transition group-hover:text-indigo-600">
                Lihat Detail
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </span>
        </div>
    </div>
</a>
