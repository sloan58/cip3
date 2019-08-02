<?php

namespace App\Jobs;

use SoapFault;
use Carbon\Carbon;
use App\Models\Ucm;
use Illuminate\Bus\Queueable;
use App\ApiClients\RisPortSoap;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateRealtimeDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     *  The number of times to attempt this job
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @var Ucm
     */
    private $ucm;

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
     * @throws GuzzleException
     */
    public function handle()
    {
        Log::info("UpdateRealtimeDataJob@handle ({$this->ucm->name}): Starting job");

        $ris = new RisPortSoap($this->ucm);
        $ris->collectRealtimeData(
            $this->ucm->phones->pluck('name')->toArray()
        );

        Log::info(
            "UpdateRealtimeDataJob@handle ({$this->ucm->name}): Setting sync_in_progress = false"
        );
        $this->ucm->sync_in_progress = false;

        Log::info(
            "UpdateRealtimeDataJob@handle ({$this->ucm->name}): Updating sync history"
        );
        $this->ucm->updateSyncHistory('Completed', Carbon::now()->timestamp);

        $this->ucm->save();

        if(setting('teams_enable_notifications')) {

            Log::info(
                "UpdateRealtimeDataJob@handle ({$this->ucm->name}): Webex Teams notifications enabled.  Sending success 
                message now."
            );

            $message = "{$this->ucm->name} just finished syncing **successfully!**";

            $this->ucm->sendWebexTeamsNotification($message);
        }
        Log::info("UpdateRealtimeDataJob@handle ({$this->ucm->name}): Realtime sync complete");
    }
}
