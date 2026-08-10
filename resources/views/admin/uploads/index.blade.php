@php $title = 'File Upload'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        {{-- Header --}}
        <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Manajemen File Upload</h2>
                <p class="text-sm text-gray-500">File besar (hingga 1 GB) yang di-upload secara chunked oleh pengguna.</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="border-b border-gray-100 px-6 py-4">
            <form method="GET" action="{{ route('admin.uploads.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama file..." class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 lg:max-w-xs">
                <select name="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    @foreach (\App\Models\Upload::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">Filter</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.uploads.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-400">
                        <th class="px-6 py-3 font-medium">File</th>
                        <th class="px-6 py-3 font-medium">Uploader</th>
                        <th class="px-6 py-3 font-medium">Ukuran</th>
                        <th class="px-6 py-3 font-medium">Progress</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Tanggal</th>
                        <th class="px-6 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($uploads as $upload)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-500 ring-1 ring-indigo-100">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-gray-900">{{ $upload->original_name }}</p>
                                        <p class="truncate font-mono text-xs text-gray-400">{{ $upload->uuid }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $upload->user->name }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ number_format($upload->file_size, 0, ',', '.') }} B</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-indigo-500" style="width: {{ $upload->progressPercent() }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $upload->progressPercent() }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-gray-100 text-gray-600 ring-gray-200',
                                        'uploading' => 'bg-blue-50 text-blue-700 ring-blue-200',
                                        'paused' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'failed' => 'bg-rose-50 text-rose-600 ring-rose-200',
                                        'cancelled' => 'bg-gray-100 text-gray-500 ring-gray-200',
                                        'expired' => 'bg-rose-50 text-rose-600 ring-rose-200',
                                    ];
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $statusColors[$upload->status] ?? 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                                    {{ ucfirst($upload->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $upload->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.uploads.show', $upload) }}" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-200">Detail</a>
                                    @if ($upload->isCompleted() && $upload->stored_name)
                                        <a href="{{ route('admin.uploads.download', $upload) }}" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 ring-1 ring-indigo-100 transition hover:bg-indigo-100">Unduh</a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.uploads.destroy', $upload) }}" data-confirm="Yakin ingin menghapus upload &quot;{{ $upload->original_name }}&quot;?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-600 ring-1 ring-rose-100 transition hover:bg-rose-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                {{ request()->hasAny(['search', 'status']) ? 'Tidak ada upload yang cocok dengan filter.' : 'Belum ada file yang di-upload.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($uploads->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $uploads->links() }}
            </div>
        @endif
    </div>
@endsection
