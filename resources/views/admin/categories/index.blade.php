@php $title = 'Kategori'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        {{-- Header --}}
        <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Manajemen Kategori</h2>
                <p class="text-sm text-gray-500">Kelola kategori layanan di CampusService.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Tambah Kategori
            </a>
        </div>

        {{-- Search --}}
        <div class="border-b border-gray-100 px-6 py-4">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="flex max-w-md gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau deskripsi kategori..." class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">Cari</button>
                @if (request('search'))
                    <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-400">
                        <th class="px-6 py-3 font-medium">#</th>
                        <th class="px-6 py-3 font-medium">Nama</th>
                        <th class="px-6 py-3 font-medium">Deskripsi</th>
                        <th class="px-6 py-3 font-medium">Produk</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($categories as $category)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-3 text-gray-400">{{ $categories->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="max-w-xs truncate px-6 py-3 text-gray-600" title="{{ $category->description }}">{{ $category->description ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $category->products_count }} produk</span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $category->status ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-gray-100 text-gray-500 ring-gray-200' }}">
                                    {{ $category->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 ring-1 ring-indigo-100 transition hover:bg-indigo-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Yakin ingin menghapus kategori &quot;{{ $category->name }}&quot;?">
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
                                {{ request('search') ? 'Tidak ada kategori yang cocok dengan pencarian &quot;' . e(request('search')) . '&quot;.' : 'Belum ada kategori. Klik &quot;Tambah Kategori&quot; untuk membuat yang pertama.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($categories->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection
