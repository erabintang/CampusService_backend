<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_UPLOADING = 'uploading';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_UPLOADING,
        self::STATUS_PAUSED,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
    ];

    /** @var list<string> */
    protected $fillable = [
        'uuid',
        'user_id',
        'original_name',
        'stored_name',
        'mime_type',
        'file_size',
        'chunk_size',
        'total_chunks',
        'uploaded_chunks',
        'chunks_received',
        'checksum',
        'status',
        'storage_disk',
        'storage_path',
        'metadata',
        'started_at',
        'completed_at',
        'failed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'chunk_size' => 'integer',
            'total_chunks' => 'integer',
            'uploaded_chunks' => 'integer',
            'chunks_received' => 'array',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * The user who owns this upload.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether the upload has finished successfully.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Progress percentage (0-100) based on received chunks.
     */
    public function progressPercent(): int
    {
        if ($this->total_chunks === 0) {
            return 0;
        }

        return (int) round(($this->uploaded_chunks / $this->total_chunks) * 100);
    }

    /**
     * Absolute path to the chunk directory (within the configured disk).
     */
    public function chunksPath(): string
    {
        return $this->storage_path.'/chunks';
    }

    /**
     * Absolute path of the final file (within the configured disk).
     */
    public function finalPath(): string
    {
        return $this->storage_path.'/final/'.$this->stored_name;
    }

    /**
     * The storage disk instance.
     */
    public function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk($this->storage_disk);
    }

    /**
     * Remove every file belonging to this upload from disk.
     */
    public function deleteFiles(): void
    {
        $this->disk()->deleteDirectory($this->storage_path);
    }

    /**
     * Whether this upload can still accept chunks.
     */
    public function isWritable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_UPLOADING, self::STATUS_PAUSED], true);
    }
}
