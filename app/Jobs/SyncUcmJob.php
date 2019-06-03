<?php

namespace App\Jobs;

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

        $axl = new AxlSoap($this->ucm);
        $axl->syncPhones();

        $ris = new RisPortSoap($this->ucm);
        $ris->collectRealtimeData(
            $this->ucm->phones->pluck('name')->toArray()
        );

        $this->ucm->sync_in_progress = false;
        $this->ucm->save();
    }
}
