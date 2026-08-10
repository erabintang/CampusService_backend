@php $title = 'Detail Upload'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('admin.uploads.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-gray-900">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke File Upload
            </a>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            {{-- Header --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-5">
                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-gray-900">{{ $upload->original_name }}</p>
                    <p class="truncate font-mono text-xs text-gray-400">{{ $upload->uuid }}</p>
                </div>
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
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusColors[$upload->status] ?? 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                    {{ ucfirst($upload->status) }}
                </span>
            </div>

            {{-- Progress --}}
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-700">Progress upload</span>
                    <span class="text-gray-500">{{ $upload->uploaded_chunks }}/{{ $upload->total_chunks }} chunk ({{ $upload->progressPercent() }}%)</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-indigo-500 transition-all" style="width: {{ $upload->progressPercent() }}%"></div>
                </div>
            </div>

            {{-- Detail --}}
            <dl class="grid grid-cols-1 gap-5 px-6 py-6 text-sm sm:grid-cols-2">
                <div class="rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Uploader</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $upload->user->name }}</dd>
                    <dd class="mt-0.5 text-gray-500">{{ $upload->user->email }}</dd>
                </div>
                <div class="rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">File</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ number_format($upload->file_size, 0, ',', '.') }} byte</dd>
                    <dd class="mt-0.5 text-gray-500">{{ $upload->mime_type ?? 'belum terdeteksi' }}</dd>
                </div>
                <div class="rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Chunk</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ $upload->chunk_size }} byte / chunk</dd>
                    <dd class="mt-0.5 text-gray-500">{{ $upload->total_chunks }} chunk total</dd>
                </div>
                <div class="rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Waktu</dt>
                    <dd class="mt-1 font-medium text-gray-900">Mulai {{ optional($upload->started_at)->format('d M Y, H:i') ?? '-' }}</dd>
                    <dd class="mt-0.5 text-gray-500">Selesai {{ optional($upload->completed_at)->format('d M Y, H:i') ?? '-' }}</dd>
                </div>
            </dl>

            {{-- Checksum --}}
            @if ($upload->checksum)
                <div class="border-t border-gray-100 px-6 py-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Checksum SHA-256</p>
                    <p class="mt-1 break-all font-mono text-xs text-gray-600">{{ $upload->checksum }}</p>
                </div>
            @endif

            {{-- Metadata gagal --}}
            @if (isset($upload->metadata['failure_reason']))
                <div class="border-t border-gray-100 px-6 py-4">
                    <p class="text-sm text-rose-600">Alasan gagal: {{ $upload->metadata['failure_reason'] }}</p>
                </div>
            @endif

            {{-- Aksi --}}
            <div class="flex flex-wrap gap-3 border-t border-gray-100 px-6 py-5">
                @if ($upload->isCompleted() && $upload->stored_name)
                    <a href="{{ route('admin.uploads.download', $upload) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Unduh File
                    </a>
                @endif
                @if (! $upload->isCompleted())
                    <form method="POST" action="{{ route('admin.uploads.destroy', $upload) }}" data-confirm="Batalkan upload ini? Semua chunk akan dihapus.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-50 px-4 py-2 text-sm font-medium text-rose-600 ring-1 ring-rose-100 transition hover:bg-rose-100">
                            Batalkan Upload
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
