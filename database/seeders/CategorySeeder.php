<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed 8 kategori layanan sesuai spesifikasi project.
     *
     * Idempotent: updateOrCreate berdasarkan nama.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Desain',
                'description' => 'Desain presentasi, poster, logo, dan kebutuhan visual lainnya untuk tugas atau kegiatan kampus.',
                'status' => true,
            ],
            [
                'name' => 'Programming',
                'description' => 'Bantuan memahami konsep pemrograman, debugging kode, dan pengembangan sederhana.',
                'status' => true,
            ],
            [
                'name' => 'Editing',
                'description' => 'Editing video, audio, dan media lainnya agar hasilnya rapi dan layak dikumpulkan.',
                'status' => true,
            ],
            [
                'name' => 'Konsultasi',
                'description' => 'Konsultasi akademik dan diskusi mendalam untuk membantu kamu memahami materi kuliah.',
                'status' => true,
            ],
            [
                'name' => 'Dokumentasi',
                'description' => 'Pendokumentasian kegiatan, fotografi, dan penyusunan laporan kegiatan organisasi.',
                'status' => true,
            ],
            [
                'name' => 'Formatting',
                'description' => 'Perapian format dokumen seperti skripsi, makalah, dan laporan sesuai panduan yang berlaku.',
                'status' => true,
            ],
            [
                'name' => 'Teknis',
                'description' => 'Bantuan teknis instalasi, konfigurasi, dan troubleshooting perangkat lunak.',
                'status' => true,
            ],
            [
                'name' => 'Lainnya',
                'description' => 'Layanan bantuan akademik lain yang wajar dan tidak termasuk kategori di atas.',
                'status' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
