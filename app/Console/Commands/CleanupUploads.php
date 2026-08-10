<?php

namespace App\Console\Commands;

use App\Services\ChunkedUploadService;
use Illuminate\Console\Command;

class CleanupUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan upload yang ditinggalkan, gagal, atau dibatalkan beserta chunk-nya';

    /**
     * Execute the console command.
     */
    public function handle(ChunkedUploadService $uploads): int
    {
        $result = $uploads->cleanup();

        $this->info(sprintf(
            'Cleanup selesai: %d upload ditandai expired, %d upload dihapus.',
            $result['expired'],
            $result['deleted'],
        ));

        return self::SUCCESS;
    }
}
