<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use App\Services\ChunkedUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadController extends Controller
{
    public function __construct(private readonly ChunkedUploadService $uploads)
    {
    }

    /**
     * Daftar semua upload: search nama + filter status + pagination.
     */
    public function index(Request $request)
    {
        $uploads = Upload::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where('original_name', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.uploads.index', compact('uploads'));
    }

    /**
     * Detail upload: progress, status, uploader, dan aksi.
     */
    public function show(Upload $upload)
    {
        return view('admin.uploads.show', compact('upload'));
    }

    /**
     * Hapus upload (file + baris) dari sisi admin.
     */
    public function destroy(Upload $upload, Request $request)
    {
        $upload->deleteFiles();
        $upload->delete();

        return redirect()
            ->route('admin.uploads.index')
            ->with('success', 'Upload berhasil dihapus.');
    }

    /**
     * Unduh file final (streaming). Hanya untuk upload yang completed.
     */
    public function download(Upload $upload): StreamedResponse|JsonResponse
    {
        if (! $upload->isCompleted() || ! $upload->stored_name) {
            return response()->json(['message' => 'File belum selesai di-upload.'], 409);
        }

        $disk = $upload->disk();
        $path = $upload->finalPath();

        if (! $disk->exists($path)) {
            return response()->json(['message' => 'File tidak ditemukan di disk.'], 404);
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
}
