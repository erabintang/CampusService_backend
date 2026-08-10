<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUploadController extends Controller
{
    /**
     * GET /api/admin/uploads — daftar semua upload untuk admin (JSON),
     * dengan search + filter status + pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $uploads = Upload::query()
            ->with('user:id,name,email')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where('original_name', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 10) ?: 10, 100))
            ->withQueryString();

        return response()->json([
            'data' => collect($uploads->items())->map(fn (Upload $upload) => [
                'id' => $upload->id,
                'uuid' => $upload->uuid,
                'original_name' => $upload->original_name,
                'user' => $upload->user
                    ? ['id' => $upload->user->id, 'name' => $upload->user->name, 'email' => $upload->user->email]
                    : null,
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
                'updated_at' => optional($upload->updated_at)->toISOString(),
                'completed_at' => optional($upload->completed_at)->toISOString(),
            ])->values(),
            'meta' => [
                'current_page' => $uploads->currentPage(),
                'last_page' => $uploads->lastPage(),
                'per_page' => $uploads->perPage(),
                'total' => $uploads->total(),
            ],
        ]);
    }
}
