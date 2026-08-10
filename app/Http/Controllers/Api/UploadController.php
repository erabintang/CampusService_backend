<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteUploadRequest;
use App\Http\Requests\StoreUploadRequest;
use App\Http\Requests\UploadChunkRequest;
use App\Models\Upload;
use App\Services\ChunkedUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadController extends Controller
{
    public function __construct(private readonly ChunkedUploadService $uploads)
    {
    }

    /**
     * POST /api/uploads — buat session upload baru.
     */
    public function store(StoreUploadRequest $request): JsonResponse
    {
        $upload = $this->uploads->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Upload session dibuat.',
            'data' => $this->present($upload),
        ], 201);
    }

    /**
     * GET /api/uploads/{upload} — status, progress, dan chunk yang sudah diterima.
     * Dipakai client untuk resume setelah koneksi terputus.
     */
    public function show(Request $request, Upload $upload): JsonResponse
    {
        $this->authorizeAccess($request, $upload);

        return response()->json([
            'message' => 'Status upload.',
            'data' => $this->present($upload),
        ]);
    }

    /**
     * POST /api/uploads/{upload}/chunks — kirim satu chunk.
     */
    public function chunks(UploadChunkRequest $request, Upload $upload): JsonResponse
    {
        $upload = $this->uploads->storeChunk(
            $upload,
            (int) $request->integer('index'),
            $request->file('chunk'),
            $request->input('chunk_checksum'),
        );

        return response()->json([
            'message' => 'Chunk diterima.',
            'data' => $this->present($upload),
        ]);
    }

    /**
     * POST /api/uploads/{upload}/complete — finalisasi upload.
     */
    public function complete(CompleteUploadRequest $request, Upload $upload): JsonResponse
    {
        $upload = $this->uploads->complete($upload, $request->input('checksum'));

        return response()->json([
            'message' => 'Upload selesai.',
            'data' => $this->present($upload),
        ]);
    }

    /**
     * GET /api/uploads/config — konfigurasi upload dari server
     * (sumber kebenaran untuk klien chunked upload).
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'data' => [
                'chunk_size' => (int) config('uploads.chunk_size'),
                'max_file_size' => (int) config('uploads.max_file_size'),
                'max_active_per_user' => (int) config('uploads.max_active_per_user'),
                'allowed_extensions' => config('uploads.allowed_extensions'),
            ],
        ]);
    }

    /**
     * DELETE /api/uploads/{upload} — batalkan upload dan bersihkan chunk.
     */
    public function destroy(Request $request, Upload $upload): JsonResponse
    {
        $this->authorizeAccess($request, $upload);

        $upload = $this->uploads->cancel($upload);

        return response()->json([
            'message' => 'Upload dibatalkan.',
            'data' => $this->present($upload),
        ]);
    }

    /**
     * GET /api/uploads/{upload}/download — unduh file final (streaming, bukan
     * dimuat ke memory). Hanya pemilik atau admin.
     */
    public function download(Request $request, Upload $upload): StreamedResponse|JsonResponse
    {
        $this->authorizeAccess($request, $upload);

        if (! $upload->isCompleted() || ! $upload->stored_name) {
            return response()->json([
                'message' => 'File belum selesai di-upload.',
            ], 409);
        }

        $disk = $upload->disk();
        $path = $upload->finalPath();

        if (! $disk->exists($path)) {
            return response()->json([
                'message' => 'File tidak ditemukan di disk.',
            ], 404);
        }

        return response()->streamDownload(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            if ($stream !== false) {
                while (! feof($stream)) {
                    echo fread($stream, 1024 * 1024);
                }
                fclose($stream);
            }
        }, $upload->original_name, [
            'Content-Type' => $upload->mime_type ?? 'application/octet-stream',
        ]);
    }

    /**
     * Ownership: pemilik upload atau admin.
     * User lain -> 404 (tidak membocorkan keberadaan upload).
     */
    private function authorizeAccess(Request $request, Upload $upload): void
    {
        $user = $request->user();

        if ($user === null || (! $user->isAdmin() && $upload->user_id !== $user->id)) {
            abort(404);
        }
    }

    /**
     * Bentuk respons JSON yang konsisten.
     *
     * @return array<string, mixed>
     */
    private function present(Upload $upload): array
    {
        return [
            'id' => $upload->id,
            'uuid' => $upload->uuid,
            'original_name' => $upload->original_name,
            'file_size' => $upload->file_size,
            'chunk_size' => $upload->chunk_size,
            'total_chunks' => $upload->total_chunks,
            'uploaded_chunks' => $upload->uploaded_chunks,
            'received_chunks' => $upload->chunks_received ?? [],
            'checksum' => $upload->checksum,
            'status' => $upload->status,
            'progress_percent' => $upload->progressPercent(),
            'stored_name' => $upload->stored_name,
            'mime_type' => $upload->mime_type,
            'created_at' => optional($upload->created_at)->toISOString(),
            'completed_at' => optional($upload->completed_at)->toISOString(),
        ];
    }
}
