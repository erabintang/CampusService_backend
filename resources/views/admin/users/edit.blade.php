@php $title = 'Edit User'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-100 px-6 py-5">
            <h2 class="text-lg font-semibold text-gray-800">Edit User</h2>
            <p class="text-sm text-gray-500">Perbarui informasi user &quot;{{ $user->name }}&quot;.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-rose-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Nomor HP</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    @php
                        $statusChecked = old('status') !== null ? old('status') == 1 : (bool) $user->status;
                    @endphp
                    <label class="flex items-center gap-2.5">
                        <input type="checkbox" name="status" value="1" @checked($statusChecked)
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">Status Aktif</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-400">User nonaktif tidak dapat login.</p>
                </div>

                <div class="rounded-lg bg-gray-50 px-4 py-3 text-xs text-gray-500 ring-1 ring-gray-200">
                    Role user tidak dapat diubah dari sini untuk menjaga keamanan sistem.
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
