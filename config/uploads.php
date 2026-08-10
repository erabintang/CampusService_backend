<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chunked Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Ukuran chunk ditentukan dari audit konfigurasi server (PHASE 0):
    | FrankenPHP post_max_size = 8M. Body multipart (file chunk + boundary +
    | field index) harus lebih kecil dari post_max_size, sehingga chunk 4 MB
    | adalah pilihan aman (terverifikasi via E2E: chunk 8 MB ditolak karena
    | melebihi post_max_size 8M setelah memperhitungkan overhead multipart).
    | XAMPP (fallback php artisan serve) memiliki post_max_size = 40M,
    | sehingga 4 MB aman di kedua runtime.
    |
    | Jangan menaikkan chunk mendekati post_max_size server.
    |
    */

    // Ukuran chunk dalam byte (4 MB).
    'chunk_size' => env('UPLOAD_CHUNK_SIZE', 4 * 1024 * 1024),

    // Ukuran maksimal satu file utuh (1 GB).
    'max_file_size' => env('UPLOAD_MAX_FILE_SIZE', 1024 * 1024 * 1024),

    // Maksimal upload aktif (belum selesai/dibatalkan) per user.
    'max_active_per_user' => env('UPLOAD_MAX_ACTIVE', 5),

    // Disk tempat chunk + file final disimpan (local = storage/app/private).
    'disk' => env('UPLOAD_DISK', 'local'),

    // Subfolder relatif terhadap root disk.
    'base_path' => 'uploads',

    // Upload yang ditinggalkan (pending/uploading/paused) dianggap hangus
    // setelah sekian jam -> status expired + file dibersihkan oleh uploads:cleanup.
    'cleanup_after_hours' => env('UPLOAD_CLEANUP_AFTER_HOURS', 24),

    // Ekstensi yang diizinkan (whitelist). Nama asli file tidak dipercaya;
    // ekstensi diambil dari nama, lalu dicocokkan ke daftar ini.
    'allowed_extensions' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'csv', 'md',
        'zip', 'rar', '7z', 'tar', 'gz',
        'jpg', 'jpeg', 'png', 'webp', 'gif',
        'mp3', 'mp4', 'mov', 'avi',
    ],
];
