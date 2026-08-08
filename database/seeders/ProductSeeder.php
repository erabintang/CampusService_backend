<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed layanan contoh untuk setiap kategori.
     *
     * Idempotent: updateOrCreate berdasarkan slug.
     * Nomor WhatsApp memakai nomor dummy untuk development.
     */
    public function run(): void
    {
        $products = [
            // ===== Desain =====
            [
                'category' => 'Desain',
                'name' => 'Desain Presentasi Profesional',
                'slug' => 'desain-presentasi',
                'price' => 50000,
                'description' => 'Pembuatan desain presentasi yang rapi, modern, dan sesuai kebutuhanmu. Cocok untuk presentasi tugas, proposal, atau kegiatan organisasi.',
                'included' => "15 slide\n2x revisi\nFile PPTX\nFile PDF",
                'payment_info' => 'Harga sudah termasuk desain dan revisi. Pembayaran dilakukan setelah menghubungi penyedia.',
                'duration' => '2 hari',
                'stock' => 10,
                'whatsapp' => '081234567801',
                'status' => true,
            ],
            [
                'category' => 'Desain',
                'name' => 'Desain Poster Kegiatan',
                'slug' => 'desain-poster-kegiatan',
                'price' => 40000,
                'description' => 'Desain poster untuk kegiatan organisasi, seminar, atau event kampus. Ukuran A3 atau A2 siap cetak.',
                'included' => "1 desain utama\n2x revisi\nFile PNG resolusi tinggi\nFile sumber (AI/PSD)",
                'payment_info' => 'Harga sudah termasuk revisi. Pembayaran setelah desain disetujui.',
                'duration' => '2 hari',
                'stock' => 8,
                'whatsapp' => '081234567801',
                'status' => true,
            ],
            // ===== Programming =====
            [
                'category' => 'Programming',
                'name' => 'Konsultasi Programming',
                'slug' => 'konsultasi-programming',
                'price' => 30000,
                'description' => 'Sesi konsultasi untuk memahami konsep programming, struktur kode, dan cara menyelesaikan masalah dengan benar.',
                'included' => "1 jam sesi\nMateri ringkas\nContoh kode",
                'payment_info' => 'Pembayaran setelah sesi dikonfirmasi.',
                'duration' => '1 jam',
                'stock' => 15,
                'whatsapp' => '081234567802',
                'status' => true,
            ],
            [
                'category' => 'Programming',
                'name' => 'Bantuan Debugging Kode',
                'slug' => 'bantuan-debugging-kode',
                'price' => 35000,
                'description' => 'Membantu menemukan dan menjelaskan bug pada kode kamu beserta solusi yang benar — kamu tetap mengerjakan tugasnya sendiri.',
                'included' => "Sesi 1 jam\nPenjelasan akar masalah\nSolusi & pencegahan",
                'payment_info' => 'Harga per sesi. Pembayaran setelah sesi selesai.',
                'duration' => '1 jam',
                'stock' => 10,
                'whatsapp' => '081234567802',
                'status' => true,
            ],
            // ===== Editing =====
            [
                'category' => 'Editing',
                'name' => 'Editing Video Tugas',
                'slug' => 'editing-video',
                'price' => 100000,
                'description' => 'Editing video tugas dengan subtitle, transisi rapi, dan sinkronisasi audio. Hasil siap dikumpulkan.',
                'included' => "Video final MP4\n1x revisi\nSubtitle opsional",
                'payment_info' => 'Harga berdasarkan durasi video. Pembayaran setelah kesepakatan.',
                'duration' => '3 hari',
                'stock' => 5,
                'whatsapp' => '081234567803',
                'status' => true,
            ],
            [
                'category' => 'Editing',
                'name' => 'Editing Audio Podcast',
                'slug' => 'editing-audio-podcast',
                'price' => 60000,
                'description' => 'Rapikan rekaman audio: hilangkan noise, potong bagian tidak perlu, dan seimbangkan volume.',
                'included' => "Noise reduction\nPemotongan & penataan\nMastering dasar",
                'payment_info' => 'Harga per 30 menit audio. Pembayaran setelah selesai.',
                'duration' => '2 hari',
                'stock' => 6,
                'whatsapp' => '081234567803',
                'status' => true,
            ],
            // ===== Konsultasi =====
            [
                'category' => 'Konsultasi',
                'name' => 'Konsultasi Skripsi',
                'slug' => 'konsultasi-skripsi',
                'price' => 45000,
                'description' => 'Diskusi terarah tentang bab, metodologi, dan struktur skripsi agar kamu lebih paham dan percaya diri.',
                'included' => "Sesi 1 jam\nCatatan diskusi\nSaran perbaikan",
                'payment_info' => 'Pembayaran setelah sesi dikonfirmasi.',
                'duration' => '1 jam',
                'stock' => 8,
                'whatsapp' => '081234567804',
                'status' => true,
            ],
            [
                'category' => 'Konsultasi',
                'name' => 'Tutoring Matematika',
                'slug' => 'tutoring-matematika',
                'price' => 40000,
                'description' => 'Sesi belajar privat untuk memahami konsep matematika dengan latihan soal yang terarah.',
                'included' => "Sesi 1,5 jam\nLatihan soal\nPembahasan step-by-step",
                'payment_info' => 'Harga per sesi. Pembayaran setelah sesi.',
                'duration' => '1 hari',
                'stock' => 10,
                'whatsapp' => '081234567804',
                'status' => true,
            ],
            // ===== Dokumentasi =====
            [
                'category' => 'Dokumentasi',
                'name' => 'Dokumentasi Kegiatan',
                'slug' => 'dokumentasi-kegiatan',
                'price' => 75000,
                'description' => 'Fotografi dan dokumentasi kegiatan kampus (acara, seminar, organisasi) dengan hasil foto yang rapi.',
                'included' => "Foto selama 4 jam\nSeleksi & edit dasar\nFile resolusi tinggi",
                'payment_info' => 'Harga per kegiatan. Pembayaran setelah penyerahan foto.',
                'duration' => '3 hari',
                'stock' => 4,
                'whatsapp' => '081234567805',
                'status' => true,
            ],
            [
                'category' => 'Dokumentasi',
                'name' => 'Penyusunan Laporan Kegiatan',
                'slug' => 'penyusunan-laporan-kegiatan',
                'price' => 55000,
                'description' => 'Bantuan menyusun laporan kegiatan yang rapi dan terstruktur dari data yang kamu miliki.',
                'included' => "Template laporan\nPenyusunan isi\nFile Word siap kumpul",
                'payment_info' => 'Pembayaran setelah laporan selesai.',
                'duration' => '2 hari',
                'stock' => 6,
                'whatsapp' => '081234567805',
                'status' => true,
            ],
            // ===== Formatting =====
            [
                'category' => 'Formatting',
                'name' => 'Formatting Skripsi',
                'slug' => 'formatting-skripsi',
                'price' => 65000,
                'description' => 'Perapian format skripsi sesuai panduan kampus: margin, heading, numbering, daftar isi otomatis, dan tabel.',
                'included' => "Margin & font sesuai panduan\nHeading & numbering\nDaftar isi otomatis",
                'payment_info' => 'Harga per dokumen. Pembayaran setelah pengecekan akhir.',
                'duration' => '3 hari',
                'stock' => 7,
                'whatsapp' => '081234567806',
                'status' => true,
            ],
            [
                'category' => 'Formatting',
                'name' => 'Formatting Makalah & Laporan',
                'slug' => 'formatting-makalah-laporan',
                'price' => 35000,
                'description' => 'Rapikan format makalah atau laporan: margin, heading, penomoran halaman, dan spasi.',
                'included' => "Margin & spasi\nHeading & penomoran\nCek konsistensi",
                'payment_info' => 'Harga per dokumen. Pembayaran setelah selesai.',
                'duration' => '1 hari',
                'stock' => 10,
                'whatsapp' => '081234567806',
                'status' => true,
            ],
            // ===== Teknis =====
            [
                'category' => 'Teknis',
                'name' => 'Instalasi & Konfigurasi Software',
                'slug' => 'instalasi-konfigurasi-software',
                'price' => 30000,
                'description' => 'Bantuan instalasi dan konfigurasi perangkat lunak (XAMPP, editor, tools kuliah) beserta panduan singkat.',
                'included' => "Instalasi & setup\nPanduan penggunaan\nTes berjalan",
                'payment_info' => 'Harga per instalasi. Pembayaran setelah berhasil.',
                'duration' => '1 hari',
                'stock' => 8,
                'whatsapp' => '081234567807',
                'status' => true,
            ],
            [
                'category' => 'Teknis',
                'name' => 'Bantuan Setup WordPress',
                'slug' => 'setup-wordpress',
                'price' => 50000,
                'description' => 'Setup WordPress (lokal atau hosting) lengkap dengan tema dasar dan panduan pengelolaan.',
                'included' => "Instalasi WordPress\nTema & plugin dasar\nPanduan pengelolaan",
                'payment_info' => 'Pembayaran setelah setup selesai.',
                'duration' => '2 hari',
                'stock' => 5,
                'whatsapp' => '081234567807',
                'status' => true,
            ],
            // ===== Lainnya =====
            [
                'category' => 'Lainnya',
                'name' => 'Proofreading Bahasa Inggris',
                'slug' => 'proofreading-bahasa-inggris',
                'price' => 40000,
                'description' => 'Pengecekan tata bahasa, ejaan, dan struktur kalimat bahasa Inggris pada tulisanmu (maks. 10 halaman).',
                'included' => "Cek grammar & ejaan\nSaran perbaikan kalimat\nCatatan perubahan",
                'payment_info' => 'Harga per 10 halaman. Pembayaran setelah selesai.',
                'duration' => '2 hari',
                'stock' => 9,
                'whatsapp' => '081234567808',
                'status' => true,
            ],
            [
                'category' => 'Lainnya',
                'name' => 'Penerjemahan Dokumen',
                'slug' => 'penerjemahan-dokumen',
                'price' => 50000,
                'description' => 'Penerjemahan dokumen (Indonesia–Inggris atau sebaliknya) dengan hasil yang akurat dan mudah dibaca.',
                'included' => "Terjemahan akurat\nKonsistensi istilah\nFile hasil siap pakai",
                'payment_info' => 'Harga per 10 halaman. Pembayaran setelah selesai.',
                'duration' => '3 hari',
                'stock' => 6,
                'whatsapp' => '081234567808',
                'status' => true,
            ],
        ];

        $categories = Category::pluck('id', 'name');

        foreach ($products as $product) {
            // Guard: pastikan kategori ada (mis. bila seeder ini dijalankan sendiri).
            throw_unless(
                $categories->has($product['category']),
                "Kategori '{$product['category']}' tidak ditemukan. Jalankan CategorySeeder terlebih dahulu."
            );

            Product::updateOrCreate(['slug' => $product['slug']], [
                'category_id' => $categories[$product['category']],
                'name' => $product['name'],
                'price' => $product['price'],
                'description' => $product['description'],
                'included' => $product['included'],
                'payment_info' => $product['payment_info'],
                'duration' => $product['duration'],
                'stock' => $product['stock'],
                'whatsapp' => $product['whatsapp'],
                'status' => $product['status'],
                // image dibiarkan null → tampil avatar huruf di frontend.
            ]);
        }
    }
}
