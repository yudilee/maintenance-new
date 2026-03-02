<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\OdooSyncService;

class SyncOdooRecords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sourceType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sourceType = 'Manual')
    {
        $this->sourceType = $sourceType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OdooSyncService $syncService)
    {
        $syncService->sync($this->sourceType);
    }
}
