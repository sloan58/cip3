<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use SoapFault;
use App\Models\Ucm;
use App\ApiClients\AxlSoap;
use Illuminate\Bus\Queueable;
use App\ApiClients\RisPortSoap;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncUcmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Ucm
     */
    private $ucm;

    /**
     *  The number of times to attempt this job
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param Ucm $ucm
     */
    public function __construct(Ucm $ucm)
    {
        $this->ucm = $ucm;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws SoapFault
     */
    public function handle()
    {
        Log::info("SyncUcmJob@handle ({$this->ucm->name}): Starting Job");

        Log::info(
            "SyncUcmJob@handle ({$this->ucm->name}): Calling AXL API to sync phones"
        );
        $axl = new AxlSoap($this->ucm);
        $axl->syncPhones();

        Log::info(
            "SyncUcmJob@handle ({$this->ucm->name}): Calling RisPort API for real time info"
        );
        $ris = new RisPortSoap($this->ucm);
        $ris->collectRealtimeData(
            $this->ucm->phones->pluck('name')->toArray()
        );

        Log::info(
            "SyncUcmJob@handle ({$this->ucm->name}): Setting sync_in_progress = false"
        );
        $this->ucm->sync_in_progress = false;

        Log::info(
            "SyncUcmJob@handle ({$this->ucm->name}): Updating sync history"
        );
        $this->ucm->updateSyncHistory(true, Carbon::now()->timestamp);

        $this->ucm->save();
    }
}
