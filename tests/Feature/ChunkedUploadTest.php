<?php

namespace Tests\Feature;

use App\Models\Upload;
use App\Models\User;
use App\Services\ChunkedUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChunkedUploadTest extends TestCase
{
    use RefreshDatabase;

    private const CONTENT = 'abcdefghij'; // 10 byte

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['uploads.chunk_size' => 4]); // file 10 byte -> 3 chunk (4,4,2)
    }

    /**
     * Mulai session upload lewat HTTP endpoint.
     *
     * @return array<string, mixed>
     */
    private function startUpload(User $user, string $name = 'laporan.pdf', int $size = 10, ?string $checksum = null): array
    {
        $response = $this->actingAs($user)->postJson('/api/uploads', [
            'original_name' => $name,
            'file_size' => $size,
            'checksum' => $checksum,
        ]);

        $response->assertStatus(201);

        return $response->json('data');
    }

    /**
     * Kirim satu chunk.
     *
     * @return \Illuminate\Testing\TestResponse
     */
    private function sendChunk(User $user, Upload $upload, int $index, string $content)
    {
        return $this->actingAs($user)->post("/api/uploads/{$upload->id}/chunks", [
            'index' => $index,
            'chunk' => UploadedFile::fake()->createWithContent("chunk-{$index}.part", $content),
        ]);
    }

    public function test_user_can_create_upload_session(): void
    {
        $user = User::factory()->create();

        $data = $this->startUpload($user);

        $this->assertSame('laporan.pdf', $data['original_name']);
        $this->assertSame(10, $data['file_size']);
        $this->assertSame(4, $data['chunk_size']);
        $this->assertSame(3, $data['total_chunks']);
        $this->assertSame(0, $data['uploaded_chunks']);
        $this->assertSame('pending', $data['status']);

        $this->assertDatabaseHas('uploads', [
            'uuid' => $data['uuid'],
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Folder chunk dibuat di disk.
        $upload = Upload::where('uuid', $data['uuid'])->firstOrFail();
        Storage::disk('local')->assertExists($upload->chunksPath());
    }

    public function test_guest_cannot_create_upload(): void
    {
        // Request JSON ke endpoint API -> 401 (bukan redirect ke login).
        $this->postJson('/api/uploads', [
            'original_name' => 'laporan.pdf',
            'file_size' => 10,
        ])->assertUnauthorized();
    }

    public function test_oversized_file_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/uploads', [
            'original_name' => 'raksasa.bin',
            'file_size' => config('uploads.max_file_size') + 1,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('file_size');
    }

    public function test_disallowed_extension_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/uploads', [
            'original_name' => 'shell.php',
            'file_size' => 10,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('original_name');
    }

    public function test_user_can_upload_all_chunks_and_resume(): void
    {
        $user = User::factory()->create();
        $data = $this->startUpload($user);
        $upload = Upload::findOrFail($data['id']);

        // Kirim chunk 0, lalu "koneksi putus" dan resume lewat GET status.
        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk();

        $resume = $this->actingAs($user)->getJson("/api/uploads/{$upload->id}")
            ->assertOk()
            ->json('data');

        $this->assertSame([0], $resume['received_chunks']);
        $this->assertSame(1, $resume['uploaded_chunks']);
        $this->assertSame('uploading', $resume['status']);

        // Lanjutkan chunk 1 dan 2.
        $this->sendChunk($user, $upload, 1, 'efgh')->assertOk();
        $this->sendChunk($user, $upload, 2, 'ij')->assertOk();

        $final = $this->actingAs($user)->postJson("/api/uploads/{$upload->id}/complete")
            ->assertOk()
            ->json('data');

        $this->assertSame('completed', $final['status']);
        $this->assertSame(100, $final['progress_percent']);

        // File final benar-benar tersedia dan isinya sesuai (refresh dulu agar
        // stored_name terisi di instance yang sama).
        $upload = $upload->refresh();
        Storage::disk('local')->assertExists($upload->finalPath());
        $this->assertSame(self::CONTENT, Storage::disk('local')->get($upload->finalPath()));
        $this->assertSame(10, Storage::disk('local')->size($upload->finalPath()));

        // Chunk temporari dibersihkan setelah finalize.
        Storage::disk('local')->assertMissing($upload->chunksPath());
    }

    public function test_duplicate_chunk_is_idempotent(): void
    {
        $user = User::factory()->create();
        $data = $this->startUpload($user);
        $upload = Upload::findOrFail($data['id']);

        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk();
        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk(); // duplikat (retry)

        $upload->refresh();
        $this->assertSame(1, $upload->uploaded_chunks);
        $this->assertSame([0], $upload->chunks_received);
    }

    public function test_invalid_chunk_index_is_rejected(): void
    {
        $user = User::factory()->create();
        $data = $this->startUpload($user);
        $upload = Upload::findOrFail($data['id']);

        $this->sendChunk($user, $upload, 99, 'abcd')->assertStatus(422);
        $this->sendChunk($user, $upload, -1, 'abcd')->assertStatus(422);
    }

    public function test_wrong_middle_chunk_size_is_rejected(): void
    {
        $user = User::factory()->create();
        $data = $this->startUpload($user);
        $upload = Upload::findOrFail($data['id']);

        // Chunk tengah (index 0) harus persis 4 byte, tapi dikirim 2 byte.
        $this->sendChunk($user, $upload, 0, 'ab')->assertStatus(422);
        $this->assertSame(0, $upload->refresh()->uploaded_chunks);
    }

    public function test_complete_with_missing_chunk_is_rejected(): void
    {
        $user = User::factory()->create();
        $data = $this->startUpload($user);
        $upload = Upload::findOrFail($data['id']);

        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk();
        $this->sendChunk($user, $upload, 1, 'efgh')->assertOk();

        // Chunk 2 belum dikirim.
        $this->actingAs($user)->postJson("/api/uploads/{$upload->id}/complete")
            ->assertStatus(422)
            ->assertJsonValidationErrors('chunks');

        $upload->refresh();
        $this->assertSame('uploading', $upload->status);
    }

    public function test_complete_verifies_checksum(): void
    {
        $user = User::factory()->create();

        // Checksum yang salah dideklarasikan saat create.
        $data = $this->startUpload($user, 'laporan.pdf', 10, str_repeat('0', 64));
        $upload = Upload::findOrFail($data['id']);

        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk();
        $this->sendChunk($user, $upload, 1, 'efgh')->assertOk();
        $this->sendChunk($user, $upload, 2, 'ij')->assertOk();

        $this->actingAs($user)->postJson("/api/uploads/{$upload->id}/complete")
            ->assertStatus(422)
            ->assertJsonValidationErrors('checksum');

        $upload->refresh();
        $this->assertSame('failed', $upload->status);
        Storage::disk('local')->assertMissing($upload->chunksPath());
    }

    public function test_complete_with_correct_checksum(): void
    {
        $user = User::factory()->create();
        $expected = hash('sha256', self::CONTENT);

        $data = $this->startUpload($user, 'laporan.pdf', 10, $expected);
        $upload = Upload::findOrFail($data['id']);

        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk();
        $this->sendChunk($user, $upload, 1, 'efgh')->assertOk();
        $this->sendChunk($user, $upload, 2, 'ij')->assertOk();

        $this->actingAs($user)->postJson("/api/uploads/{$upload->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $upload->refresh();
        $this->assertSame($expected, $upload->checksum);
        // MIME dideteksi server dari isi file (bukan dari client) — tidak null.
        $this->assertNotNull($upload->mime_type);
        $this->assertNotSame('application/octet-stream', $upload->mime_type);
    }

    public function test_other_user_cannot_access_upload(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $data = $this->startUpload($owner);
        $upload = Upload::findOrFail($data['id']);
        $this->sendChunk($owner, $upload, 0, 'abcd')->assertOk();

        // GET status -> 404 (tidak membocorkan keberadaan).
        $this->actingAs($intruder)->getJson("/api/uploads/{$upload->id}")->assertNotFound();

        // Kirim chunk -> 403 (FormRequest authorize).
        $this->sendChunk($intruder, $upload, 1, 'efgh')->assertForbidden();

        // Complete -> 403.
        $this->actingAs($intruder)->postJson("/api/uploads/{$upload->id}/complete")->assertForbidden();

        // Cancel -> 404.
        $this->actingAs($intruder)->deleteJson("/api/uploads/{$upload->id}")->assertNotFound();

        // Download -> 404.
        $this->actingAs($intruder)->get("/api/uploads/{$upload->id}/download")->assertNotFound();
    }

    public function test_user_can_cancel_upload(): void
    {
        $user = User::factory()->create();
        $data = $this->startUpload($user);
        $upload = Upload::findOrFail($data['id']);

        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk();

        $this->actingAs($user)->deleteJson("/api/uploads/{$upload->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $upload->refresh();
        $this->assertSame('cancelled', $upload->status);
        Storage::disk('local')->assertMissing($upload->chunksPath());

        // Tidak bisa kirim chunk lagi.
        $this->sendChunk($user, $upload, 1, 'efgh')->assertStatus(422);
    }

    public function test_corrupted_chunk_on_disk_is_detected(): void
    {
        $user = User::factory()->create();
        $data = $this->startUpload($user);
        $upload = Upload::findOrFail($data['id']);

        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk();
        $this->sendChunk($user, $upload, 1, 'efgh')->assertOk();
        $this->sendChunk($user, $upload, 2, 'ij')->assertOk();

        // Hapus chunk 1 dari disk -> finalize harus gagal dengan pesan jelas.
        Storage::disk('local')->delete($upload->chunksPath().'/chunk-1.part');

        $this->actingAs($user)->postJson("/api/uploads/{$upload->id}/complete")
            ->assertStatus(422)
            ->assertJsonValidationErrors('chunks');
    }

    public function test_cleanup_expires_abandoned_and_deletes_terminal(): void
    {
        $user = User::factory()->create();
        $service = app(ChunkedUploadService::class);

        // Upload aktif yang ditinggalkan (pakai query builder agar updated_at
        // tidak ditimpa now() oleh Eloquent timestamps).
        $abandoned = $service->create($user, [
            'original_name' => 'lama.pdf',
            'file_size' => 10,
        ]);
        Upload::whereKey($abandoned->id)->update(['updated_at' => now()->subHours(48)]);

        // Upload cancelled yang sudah tua.
        $cancelled = $service->create($user, [
            'original_name' => 'batal.pdf',
            'file_size' => 10,
        ]);
        $service->cancel($cancelled);
        Upload::whereKey($cancelled->id)->update(['updated_at' => now()->subHours(48)]);

        // Upload aktif yang baru (tidak boleh tersentuh).
        $fresh = $service->create($user, [
            'original_name' => 'baru.pdf',
            'file_size' => 10,
        ]);

        $result = $service->cleanup();

        $this->assertSame(1, $result['expired']);
        $this->assertSame(1, $result['deleted']);

        $this->assertSame('expired', $abandoned->refresh()->status);
        $this->assertDatabaseMissing('uploads', ['id' => $cancelled->id]);
        $this->assertSame('pending', $fresh->refresh()->status);
        Storage::disk('local')->assertExists($fresh->chunksPath());
    }

    public function test_double_complete_is_safe(): void
    {
        $user = User::factory()->create();
        $data = $this->startUpload($user);
        $upload = Upload::findOrFail($data['id']);

        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk();
        $this->sendChunk($user, $upload, 1, 'efgh')->assertOk();
        $this->sendChunk($user, $upload, 2, 'ij')->assertOk();

        $this->actingAs($user)->postJson("/api/uploads/{$upload->id}/complete")->assertOk();
        $this->actingAs($user)->postJson("/api/uploads/{$upload->id}/complete")->assertOk();

        $this->assertSame(1, Upload::where('uuid', $upload->uuid)->count());
        $this->assertSame(10, Storage::disk('local')->size($upload->refresh()->finalPath()));
    }

    public function test_download_serves_completed_file(): void
    {
        $user = User::factory()->create();
        $data = $this->startUpload($user);
        $upload = Upload::findOrFail($data['id']);

        $this->sendChunk($user, $upload, 0, 'abcd')->assertOk();
        $this->sendChunk($user, $upload, 1, 'efgh')->assertOk();
        $this->sendChunk($user, $upload, 2, 'ij')->assertOk();
        $this->actingAs($user)->postJson("/api/uploads/{$upload->id}/complete")->assertOk();

        // Isi file sudah diverifikasi di test finalize (assertSame CONTENT di disk);
        // di sini cukup verifikasi header download + status.
        $upload = $upload->refresh();
        $this->actingAs($user)->get("/api/uploads/{$upload->id}/download")
            ->assertOk()
            ->assertDownload('laporan.pdf')
            ->assertHeaderContains('Content-Type', $upload->mime_type);

        // Download sebelum selesai -> 409.
        $pending = $this->startUpload($user, 'belum.pdf', 10);
        $this->actingAs($user)->get("/api/uploads/{$pending['id']}/download")->assertStatus(409);
    }
}
