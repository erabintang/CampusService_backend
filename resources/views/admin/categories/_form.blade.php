<div class="space-y-5">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nama Kategori <span class="text-rose-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" placeholder="Contoh: Desain"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        @error('name')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
        <textarea id="description" name="description" rows="3" placeholder="Jelaskan kategori ini (opsional)..."
                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        @php
            // Jika validasi gagal, hormati pilihan user (old). Jika tidak, pakai nilai model / default.
            $statusChecked = old('status') !== null ? old('status') == 1 : (bool) ($category->status ?? true);
        @endphp
        <label class="flex items-center gap-2.5">
            <input type="checkbox" name="status" value="1" @checked($statusChecked)
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm font-medium text-gray-700">Status Aktif</span>
        </label>
        <p class="mt-1 text-xs text-gray-400">Nonaktifkan untuk menyembunyikan kategori dari katalog layanan.</p>
    </div>
</div>
