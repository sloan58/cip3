<?php

namespace App\Jobs;

use SoapFault;
use App\Models\Ucm;
use Illuminate\Bus\Queueable;
use App\ApiClients\RisPortSoap;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateRealtimeDataJob implements ShouldQueue
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
        Log::info("UpdateRealtimeDataJob@handle ({$this->ucm->name}): Starting job");

        $ris = new RisPortSoap($this->ucm);
        $ris->collectRealtimeData(
            $this->ucm->phones->pluck('name')->toArray()
        );

        Log::info("UpdateRealtimeDataJob@handle ({$this->ucm->name}): Realtime sync complete");
    }
}
