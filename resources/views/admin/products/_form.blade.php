<div class="space-y-5">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Layanan <span class="text-rose-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" placeholder="Contoh: Desain Presentasi"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori <span class="text-rose-500">*</span></label>
            <select id="category_id" name="category_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">— Pilih Kategori —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Harga (Rp) <span class="text-rose-500">*</span></label>
            <input type="number" id="price" name="price" step="0.01" min="0" value="{{ old('price', $product->price ?? '') }}" placeholder="50000"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('price') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="stock" class="block text-sm font-medium text-gray-700">Slot Tersedia <span class="text-rose-500">*</span></label>
            <input type="number" id="stock" name="stock" min="0" value="{{ old('stock', $product->stock ?? '') }}" placeholder="10"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <p class="mt-1 text-xs text-gray-400">Slot = jumlah layanan yang bisa dipesan.</p>
            @error('stock') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="duration" class="block text-sm font-medium text-gray-700">Estimasi Pengerjaan</label>
            <input type="text" id="duration" name="duration" value="{{ old('duration', $product->duration ?? '') }}" placeholder="Contoh: 2 hari"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('duration') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="whatsapp" class="block text-sm font-medium text-gray-700">Nomor WhatsApp <span class="text-rose-500">*</span></label>
            <input type="text" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $product->whatsapp ?? '') }}" placeholder="081234567890"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <p class="mt-1 text-xs text-gray-400">Hanya angka. Nomor ini yang dihubungi user saat checkout.</p>
            @error('whatsapp') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="image" class="block text-sm font-medium text-gray-700">Gambar Layanan</label>
            @if (!empty($product->image))
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="Gambar saat ini" class="h-16 w-16 rounded-lg object-cover ring-1 ring-gray-200">
                    <span class="text-xs text-gray-400">Gambar saat ini. Pilih file baru untuk menggantinya.</span>
                </div>
            @endif
            <input type="file" id="image" name="image" accept="image/jpeg,image/jpg,image/png,image/webp"
                   class="mt-2 block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-600 hover:file:bg-indigo-100">
            <p class="mt-1 text-xs text-gray-400">JPG, JPEG, PNG, atau WEBP. Maksimal 2MB.</p>
            @error('image') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi <span class="text-rose-500">*</span></label>
        <textarea id="description" name="description" rows="4" placeholder="Jelaskan layanan ini secara lengkap..."
                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="included" class="block text-sm font-medium text-gray-700">Yang Didapat</label>
        <textarea id="included" name="included" rows="3" placeholder="Pisahkan per baris. Contoh:&#10;15 slide&#10;2x revisi&#10;File PPTX + PDF"
                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('included', $product->included ?? '') }}</textarea>
        @error('included') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="payment_info" class="block text-sm font-medium text-gray-700">Informasi Pembayaran</label>
        <textarea id="payment_info" name="payment_info" rows="2" placeholder="Contoh: Harga sudah termasuk desain. Pembayaran dilakukan setelah menghubungi penyedia."
                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('payment_info', $product->payment_info ?? '') }}</textarea>
        @error('payment_info') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        @php
            $statusChecked = old('status') !== null ? old('status') == 1 : (bool) ($product->status ?? true);
        @endphp
        <label class="flex items-center gap-2.5">
            <input type="checkbox" name="status" value="1" @checked($statusChecked)
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm font-medium text-gray-700">Status Aktif</span>
        </label>
        <p class="mt-1 text-xs text-gray-400">Nonaktifkan untuk menyembunyikan layanan dari katalog user.</p>
    </div>
</div>
