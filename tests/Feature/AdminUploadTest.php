<?php

namespace Tests\Feature;

use App\Models\Upload;
use App\Models\User;
use App\Services\ChunkedUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUploadTest extends TestCase
{
    use RefreshDatabase;

    private const CONTENT = 'abcdefghij';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['uploads.chunk_size' => 4]);
    }

    /**
     * Selesaikan satu upload penuh sebagai $owner, kembalikan model-nya.
     */
    private function completedUpload(User $owner): Upload
    {
        $service = app(ChunkedUploadService::class);
        $upload = $service->create($owner, [
            'original_name' => 'tugas-akhir.pdf',
            'file_size' => 10,
        ]);

        foreach (['abcd', 'efgh', 'ij'] as $index => $content) {
            $upload->refresh();
            $service->storeChunk(
                $upload,
                $index,
                UploadedFile::fake()->createWithContent("chunk-{$index}.part", $content),
            );
        }

        return $service->complete($upload->refresh());
    }

    public function test_guest_cannot_access_admin_uploads(): void
    {
        $this->get('/admin/uploads')->assertRedirect('/login');
    }

    public function test_normal_user_cannot_access_admin_uploads(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $upload = $this->completedUpload($owner);

        $this->actingAs($user)->get('/admin/uploads')->assertForbidden();
        $this->actingAs($user)->get("/admin/uploads/{$upload->id}")->assertForbidden();
    }

    public function test_admin_can_list_all_uploads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->completedUpload($userA);
        $this->completedUpload($userB);

        $this->actingAs($admin)
            ->get('/admin/uploads')
            ->assertOk()
            ->assertSee('tugas-akhir.pdf')
            ->assertSee($userA->name)
            ->assertSee($userB->name);
    }

    public function test_admin_can_search_and_filter_uploads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->completedUpload($user);

        $service = app(ChunkedUploadService::class);
        $cancelled = $service->create($user, ['original_name' => 'skripsi.docx', 'file_size' => 10]);
        $service->cancel($cancelled);

        $this->actingAs($admin)
            ->get('/admin/uploads?search=skripsi')
            ->assertOk()
            ->assertSee('skripsi.docx')
            ->assertDontSee('tugas-akhir.pdf');

        $this->actingAs($admin)
            ->get('/admin/uploads?status=cancelled')
            ->assertOk()
            ->assertSee('skripsi.docx')
            ->assertDontSee('tugas-akhir.pdf');
    }

    public function test_admin_can_view_detail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $upload = $this->completedUpload($user);

        $this->actingAs($admin)
            ->get("/admin/uploads/{$upload->id}")
            ->assertOk()
            ->assertSee('tugas-akhir.pdf')
            ->assertSee('Unduh File');
    }

    public function test_admin_can_download_completed_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $upload = $this->completedUpload($user);

        // Isi file diverifikasi di ChunkedUploadTest (disk); di sini cukup
        // verifikasi status + header unduhan.
        $this->actingAs($admin)
            ->get("/admin/uploads/{$upload->id}/download")
            ->assertOk()
            ->assertDownload('tugas-akhir.pdf')
            ->assertHeaderContains('Content-Type', $upload->mime_type);
    }

    public function test_admin_can_delete_upload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $upload = $this->completedUpload($user);

        $this->actingAs($admin)
            ->delete("/admin/uploads/{$upload->id}")
            ->assertRedirect(route('admin.uploads.index'));

        $this->assertDatabaseMissing('uploads', ['id' => $upload->id]);
        Storage::disk('local')->assertMissing($upload->finalPath());
    }

    public function test_admin_can_use_api_on_any_upload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $service = app(ChunkedUploadService::class);
        $upload = $service->create($user, ['original_name' => 'milik-user.pdf', 'file_size' => 10]);

        // Admin melihat status upload milik user lain (tidak 404).
        $this->actingAs($admin)
            ->getJson("/api/uploads/{$upload->id}")
            ->assertOk()
            ->assertJsonPath('data.original_name', 'milik-user.pdf');
    }
}
