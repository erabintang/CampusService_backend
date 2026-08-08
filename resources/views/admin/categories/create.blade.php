@php $title = 'Tambah Kategori'; @endphp
@extends('layouts.admin')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-100 px-6 py-5">
            <h2 class="text-lg font-semibold text-gray-800">Tambah Kategori</h2>
            <p class="text-sm text-gray-500">Buat kategori baru untuk layanan mahasiswa.</p>
        </div>

        <form method="POST" action="{{ route('admin.categories.store') }}" class="p-6">
            @csrf
            @include('admin.categories._form')

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('admin.categories.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Batal</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">Simpan Kategori</button>
            </div>
        </form>
    </div>
@endsection
