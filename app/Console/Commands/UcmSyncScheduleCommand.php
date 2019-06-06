<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Ucm;
use App\Jobs\SyncUcmJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UcmSyncScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ucm:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync UCM Servers based on sync_at field';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info("UcmSyncScheduleCommand@handle: Running ucm:sync command");

        Ucm::where(
            'sync_at',
            Carbon::now()->startOfMinute()->toTimeString()
        )->each(function($ucm) {
            Log::info(
                "UcmSyncScheduleCommand@handle: Creating sync job for $ucm->name"
            );
            $ucm->sync_in_progress = true;
            $ucm->save();
            SyncUcmJob::dispatch($ucm);
        });
    }
}
