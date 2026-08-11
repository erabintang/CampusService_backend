<?php

namespace App\Services;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Inti logika chunked / resumable upload.
 *
 * - Binary chunk disimpan sebagai file terpisah di filesystem (disk local /
 *   storage/app/private/uploads/{uuid}/chunks), TIDAK pernah dimuat ke RAM.
 * - Metadata disimpan di tabel MySQL `uploads` (satu baris per upload).
 * - Setiap request HTTP hanya membawa satu chunk kecil (<= chunk_size),
 *   sehingga tidak bergantung pada post_max_size/upload_max_filesize besar.
 */
class ChunkedUploadService
{
    private const STREAM_BLOCK = 1024 * 1024; // 1 MB per blok saat streaming.

    /**
     * Membuat session upload baru.
     */
    public function create(User $user, array $data): Upload
    {
        $fileSize = (int) $data['file_size'];
        $originalName = trim((string) $data['original_name']);

        if ($fileSize <= 0) {
            throw ValidationException::withMessages([
                'file_size' => 'Ukuran file harus lebih dari 0.',
            ]);
        }

        if ($fileSize > config('uploads.max_file_size')) {
            throw ValidationException::withMessages([
                'file_size' => 'Ukuran file melebihi batas maksimal '.$this->humanBytes(config('uploads.max_file_size')).'.',
            ]);
        }

        $extension = $this->extensionOf($originalName);
        if (! $this->extensionAllowed($extension)) {
            throw ValidationException::withMessages([
                'original_name' => 'Ekstensi file tidak diizinkan.',
            ]);
        }

        $activeCount = Upload::where('user_id', $user->id)
            ->whereIn('status', [Upload::STATUS_PENDING, Upload::STATUS_UPLOADING, Upload::STATUS_PAUSED])
            ->count();

        if ($activeCount >= (int) config('uploads.max_active_per_user')) {
            throw ValidationException::withMessages([
                'original_name' => 'Terlalu banyak upload aktif. Selesaikan atau batalkan upload sebelumnya.',
            ]);
        }

        $uuid = (string) Str::uuid();
        $chunkSize = (int) config('uploads.chunk_size');

        $upload = Upload::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'original_name' => $originalName,
            'file_size' => $fileSize,
            'chunk_size' => $chunkSize,
            'total_chunks' => (int) ceil($fileSize / $chunkSize),
            'checksum' => isset($data['checksum']) ? strtolower((string) $data['checksum']) : null,
            'status' => Upload::STATUS_PENDING,
            'uploaded_chunks' => 0,
            'storage_disk' => (string) config('uploads.disk'),
            'storage_path' => config('uploads.base_path').'/'.$uuid,
            'metadata' => [
                'extension' => $extension,
                'client_mime' => $data['mime_type'] ?? null,
                'ip' => request()->ip(),
            ],
            'started_at' => now(),
        ]);

        $upload->disk()->makeDirectory($upload->chunksPath());

        return $upload;
    }

    /**
     * Menyimpan satu chunk. Idempotent: chunk yang sama dikirim ulang
     * (mis. retry setelah koneksi putus) tidak akan merusak data.
     */
    public function storeChunk(Upload $upload, int $index, UploadedFile $chunk, ?string $chunkChecksum = null): Upload
    {
        if (! $upload->isWritable()) {
            throw ValidationException::withMessages([
                'upload' => 'Upload sudah selesai atau dibatalkan, tidak bisa menerima chunk lagi.',
            ]);
        }

        if ($index < 0 || $index >= $upload->total_chunks) {
            throw ValidationException::withMessages([
                'index' => 'Index chunk di luar rentang yang valid (0-'.($upload->total_chunks - 1).').',
            ]);
        }

        $chunkSize = $chunk->getSize();

        // Chunk tengah harus berukuran persis chunk_size; chunk terakhir boleh lebih kecil.
        $expectedMiddle = $index < $upload->total_chunks - 1 ? $upload->chunk_size : $upload->file_size - ($upload->total_chunks - 1) * $upload->chunk_size;

        if ($index < $upload->total_chunks - 1 && $chunkSize !== $upload->chunk_size) {
            throw ValidationException::withMessages([
                'chunk' => 'Chunk tengah harus berukuran persis '.$this->humanBytes($upload->chunk_size).'.',
            ]);
        }

        if ($chunkSize < 1 || $chunkSize > $upload->chunk_size) {
            throw ValidationException::withMessages([
                'chunk' => 'Ukuran chunk tidak valid (maksimal '.$this->humanBytes($upload->chunk_size).').',
            ]);
        }

        $received = $upload->chunks_received ?? [];

        // Sudah pernah diterima -> sukses tanpa menulis ulang (idempotent).
        if (in_array($index, $received, true)) {
            return $upload->refresh();
        }

        // Simpan ke disk terlebih dahulu (streaming dari temp PHP, bukan ke RAM).
        $chunkPath = $upload->chunksPath().'/chunk-'.$index.'.part';
        $upload->disk()->putFileAs($upload->chunksPath(), $chunk, 'chunk-'.$index.'.part');

        // Verifikasi ukuran tersimpan sama dengan yang dikirim.
        if ($upload->disk()->size($chunkPath) !== $chunkSize) {
            $upload->disk()->delete($chunkPath);
            throw ValidationException::withMessages([
                'chunk' => 'Ukuran chunk tersimpan tidak sesuai.',
            ]);
        }

        // Verifikasi checksum chunk (opsional, dari client) bila dikirim.
        if ($chunkChecksum !== null) {
            $computed = hash_file('sha256', $upload->disk()->path($chunkPath));
            if ($computed !== strtolower($chunkChecksum)) {
                $upload->disk()->delete($chunkPath);
                throw ValidationException::withMessages([
                    'chunk' => 'Checksum chunk tidak cocok.',
                ]);
            }
        }

        // Catat index chunk di database secara atomik (cegah duplikat akibat race).
        DB::transaction(function () use ($upload, $index, $chunkPath) {
            $fresh = Upload::lockForUpdate()->findOrFail($upload->id);
            $received = $fresh->chunks_received ?? [];

            if (in_array($index, $received, true)) {
                // Konkurensi: chunk lain sudah mencatat index yang sama -> buang duplikat.
                $fresh->disk()->delete($chunkPath);

                return;
            }

            $received[] = $index;
            sort($received);

            $fresh->chunks_received = $received;
            $fresh->uploaded_chunks = count($received);
            $fresh->status = Upload::STATUS_UPLOADING;
            $fresh->save();
        });

        return $upload->refresh();
    }

    /**
     * Menyusun file final dari semua chunk. Verifikasi jumlah, ukuran, dan
     * checksum (bila dideklarasikan) sebelum file dianggap sukses.
     */
    public function complete(Upload $upload, ?string $declaredChecksum = null): Upload
    {
        if ($upload->isCompleted()) {
            return $upload->refresh();
        }

        if (! $upload->isWritable()) {
            throw ValidationException::withMessages([
                'upload' => 'Upload tidak dalam keadaan yang bisa difinalisasi.',
            ]);
        }

        $received = array_map('intval', $upload->chunks_received ?? []);
        sort($received);

        // 1. Verifikasi jumlah chunk.
        if ($received !== range(0, $upload->total_chunks - 1)) {
            $missing = array_values(array_diff(range(0, $upload->total_chunks - 1), $received));

            throw ValidationException::withMessages([
                'chunks' => 'Upload belum lengkap. Chunk yang hilang: '.implode(', ', $missing).'.',
            ]);
        }

        $disk = $upload->disk();
        $expectedMiddle = $upload->chunk_size;

        // 2. Verifikasi semua file chunk ada dan ukurannya benar.
        foreach ($received as $i) {
            $path = $upload->chunksPath().'/chunk-'.$i.'.part';

            if (! $disk->exists($path)) {
                throw ValidationException::withMessages([
                    'chunks' => 'Chunk '.$i.' tidak ditemukan di disk.',
                ]);
            }

            $isLast = $i === $upload->total_chunks - 1;
            $expectedSize = $isLast
                ? $upload->file_size - ($upload->total_chunks - 1) * $expectedMiddle
                : $expectedMiddle;

            if ($disk->size($path) !== $expectedSize) {
                throw ValidationException::withMessages([
                    'chunks' => 'Ukuran chunk '.$i.' tidak sesuai dengan yang diharapkan.',
                ]);
            }
        }

        // 3. Susun file final secara streaming sambil menghitung sha256.
        $extension = $upload->metadata['extension'] ?? 'bin';
        $storedName = $upload->uuid.'.'.$extension;
        $finalDir = $upload->storage_path.'/final';
        $tmpPath = $finalDir.'/.'.$storedName.'.tmp';

        $disk->makeDirectory($finalDir);
        $hashCtx = hash_init('sha256');
        $out = fopen($disk->path($tmpPath), 'wb');

        try {
            foreach ($received as $i) {
                $in = fopen($disk->path($upload->chunksPath().'/chunk-'.$i.'.part'), 'rb');

                while (! feof($in)) {
                    $block = fread($in, self::STREAM_BLOCK);
                    fwrite($out, $block);
                    hash_update($hashCtx, $block);
                }

                fclose($in);
            }
        } finally {
            fclose($out);
        }

        $computedChecksum = hash_final($hashCtx);
        $expectedChecksum = strtolower((string) ($declaredChecksum ?? $upload->checksum ?? ''));

        // 4. Verifikasi ukuran final.
        if (filesize($disk->path($tmpPath)) !== $upload->file_size) {
            $this->fail($upload, 'Ukuran file final tidak sesuai dengan file_size.');
            throw ValidationException::withMessages([
                'file' => 'Ukuran file final tidak sesuai.',
            ]);
        }

        // 5. Verifikasi checksum final (bila client mendeklarasikan).
        if ($expectedChecksum !== '' && $computedChecksum !== $expectedChecksum) {
            $this->fail($upload, 'Checksum file final tidak cocok.');
            throw ValidationException::withMessages([
                'checksum' => 'Checksum file final tidak cocok. Upload dibatalkan.',
            ]);
        }

        // 6. Deteksi MIME asli dari isi file (bukan dari client).
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $disk->path($tmpPath)) ?: 'application/octet-stream';

        // 7. Rename atomik ke nama final, hapus chunk temporari, update DB.
        $disk->move($tmpPath, $finalDir.'/'.$storedName);
        $disk->deleteDirectory($upload->chunksPath());

        $upload->update([
            'status' => Upload::STATUS_COMPLETED,
            'stored_name' => $storedName,
            'mime_type' => $mime,
            'checksum' => $computedChecksum,
            'completed_at' => now(),
        ]);

        return $upload->refresh();
    }

    /**
     * Membatalkan upload: status cancelled + hapus semua file dari disk.
     */
    public function cancel(Upload $upload): Upload
    {
        if (! $upload->isCompleted()) {
            $upload->deleteFiles();
            $upload->update([
                'status' => Upload::STATUS_CANCELLED,
                'failed_at' => now(),
            ]);
        }

        return $upload->refresh();
    }

    /**
     * Membersihkan upload yang ditinggalkan / gagal / dibatalkan.
     *
     * - Upload aktif (pending/uploading/paused) yang tidak di-update selama
     *   cleanup_after_hours -> status expired + file dibersihkan.
     * - Upload terminal (failed/cancelled/expired) yang lebih tua dari
     *   cleanup_after_hours -> baris + file dihapus.
     *
     * @return array{expired: int, deleted: int}
     */
    public function cleanup(): array
    {
        $cutoff = now()->subHours((int) config('uploads.cleanup_after_hours'));
        $expired = 0;
        $deleted = 0;

        Upload::whereIn('status', [Upload::STATUS_PENDING, Upload::STATUS_UPLOADING, Upload::STATUS_PAUSED])
            ->where('updated_at', '<', $cutoff)
            ->get()
            ->each(function (Upload $upload) use (&$expired) {
                $upload->deleteFiles();
                $upload->update([
                    'status' => Upload::STATUS_EXPIRED,
                    'failed_at' => now(),
                ]);
                $expired++;
            });

        Upload::whereIn('status', [Upload::STATUS_FAILED, Upload::STATUS_CANCELLED, Upload::STATUS_EXPIRED])
            ->where('updated_at', '<', $cutoff)
            ->get()
            ->each(function (Upload $upload) use (&$deleted) {
                $upload->deleteFiles();
                $upload->delete();
                $deleted++;
            });

        return compact('expired', 'deleted');
    }

    /**
     * Tandai upload gagal + bersihkan file.
     */
    private function fail(Upload $upload, string $reason): void
    {
        $upload->deleteFiles();
        $upload->update([
            'status' => Upload::STATUS_FAILED,
            'failed_at' => now(),
            'metadata' => array_merge($upload->metadata ?? [], ['failure_reason' => $reason]),
        ]);
    }

    private function extensionOf(string $name): string
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return $extension !== '' ? $extension : 'bin';
    }

    private function extensionAllowed(string $extension): bool
    {
        return in_array($extension, config('uploads.allowed_extensions'), true);
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / 1024 / 1024 / 1024, 1).' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return round($bytes / 1024 / 1024, 1).' MB';
        }

        return $bytes.' byte';
    }
}
