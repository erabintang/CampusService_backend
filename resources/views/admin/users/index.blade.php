@php $title = 'User'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        {{-- Header --}}
        <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Manajemen User</h2>
                <p class="text-sm text-gray-500">Kelola pengguna (pelanggan) CampusService.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                {{ number_format($users->total()) }} user terdaftar
            </span>
        </div>

        {{-- Search --}}
        <div class="border-b border-gray-100 px-6 py-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex max-w-md gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email user..." class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">Cari</button>
                @if (request('search'))
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs uppercase tracking-wide text-gray-400">
                        <th class="px-6 py-3 font-medium">User</th>
                        <th class="px-6 py-3 font-medium">No. HP</th>
                        <th class="px-6 py-3 font-medium">Role</th>
                        <th class="px-6 py-3 font-medium">Pesanan</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Terdaftar</th>
                        <th class="px-6 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($users as $user)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $user->isAdmin() ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500' }} text-sm font-bold">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="truncate text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $user->phone ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $user->isAdmin() ? 'bg-indigo-50 text-indigo-600 ring-indigo-200' : 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $user->orders_count }} pesanan</span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $user->status ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-600 ring-rose-200' }}">
                                    {{ $user->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-200">Detail</a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 ring-1 ring-indigo-100 transition hover:bg-indigo-100">Edit</a>
                                    @if ($user->id !== auth()->id() && ! $user->isAdmin())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Yakin ingin menghapus user &quot;{{ $user->name }}&quot;?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-600 ring-1 ring-rose-100 transition hover:bg-rose-100">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                {{ request('search') ? 'Tidak ada user yang cocok dengan pencarian.' : 'Belum ada user terdaftar.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
