@php $title = 'Produk'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        {{-- Header --}}
        <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Manajemen Produk</h2>
                <p class="text-sm text-gray-500">Kelola layanan yang ditawarkan mahasiswa.</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Tambah Produk
            </a>
        </div>

        {{-- Filter --}}
        <div class="border-b border-gray-100 px-6 py-4">
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama layanan..." class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 lg:max-w-xs">
                <select name="category_id" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="1" @selected(request('status') === '1')>Aktif</option>
                    <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">Filter</button>
                    @if (request()->hasAny(['search', 'category_id', 'status']))
                        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-400">
                        <th class="px-6 py-3 font-medium">Produk</th>
                        <th class="px-6 py-3 font-medium">Kategori</th>
                        <th class="px-6 py-3 font-medium">Harga</th>
                        <th class="px-6 py-3 font-medium">Slot</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($products as $product)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-11 w-11 shrink-0 rounded-lg object-cover ring-1 ring-gray-200">
                                    @else
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-500 ring-1 ring-indigo-100">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-gray-900">{{ $product->name }}</p>
                                        <p class="truncate font-mono text-xs text-gray-400">{{ $product->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $product->category->name }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-3">
                                @if ($product->stock == 0)
                                    <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-600 ring-1 ring-rose-200">Slot penuh</span>
                                @elseif ($product->stock <= 3)
                                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-200">{{ $product->stock }} slot</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $product->stock }} slot</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $product->status ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-gray-100 text-gray-500 ring-gray-200' }}">
                                    {{ $product->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.products.show', $product) }}" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-200">Lihat</a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 ring-1 ring-indigo-100 transition hover:bg-indigo-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Yakin ingin menghapus produk &quot;{{ $product->name }}&quot;?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-600 ring-1 ring-rose-100 transition hover:bg-rose-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                {{ request()->hasAny(['search', 'category_id', 'status']) ? 'Tidak ada produk yang cocok dengan filter.' : 'Belum ada produk. Klik &quot;Tambah Produk&quot; untuk membuat yang pertama.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($products->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection
