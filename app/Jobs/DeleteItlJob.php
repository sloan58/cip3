<?php

namespace App\Jobs;

use SoapFault;
use App\Models\Phone;
use App\Models\ItlHistory;
use App\ApiClients\AxlSoap;
use Illuminate\Bus\Queueable;
use App\ApiClients\RisPortSoap;
use App\ApiClients\PhoneController;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteItlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     *  The number of times to attempt this job
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @var Phone
     */
    private $phone;

    /**
     * The User email requesting the process
     */
    private $requestedBy;

    /**
     * Create a new job instance.
     *
     * @param Phone $phone
     * @param $requestedBy
     */
    public function __construct(Phone $phone, $requestedBy)
    {
        $this->phone = $phone;
        $this->requestedBy = $requestedBy;
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
        Log::info(
            'DeleteItlJob@handle: Starting Delete ITL job for device',
            [$this->phone->name]
        );

        Log::info('DeleteItlJob@handle: Creating Itl History object');
        $itlHistory = new ItlHistory;
        $itlHistory->phone = $this->phone->name;
        $itlHistory->requested_by = $this->requestedBy;

        Log::info('DeleteItlJob@handle: Pulling latest realtime information for this phone');
        $ris = new RisPortSoap($this->phone->ucm, false);
        $ris->collectRealtimeData([$this->phone->name]);

        Log::info('DeleteItlJob@handle: Checking if phone is registered and has an IP Address');
        if(!$this->phone->isRegistered() || is_null($this->phone->currentIpAddress())) {
            Log::info('DeleteItlJob@handle: The phone is either not registered or does not have an IP address',
                ['Status' => $this->phone->status, 'IPAddress' => $this->phone->currentIpAddress()]
            );

            $itlHistory->status = 'finished';
            $itlHistory->result = 'fail';
            $itlHistory->fail_reason = !$this->phone->isRegistered() ? 'Phone not registered' : 'No known IP Address';
            $itlHistory->save();
            exit;
        }

        Log::info('DeleteItlJob@handle: Phone is registered and has an IP Address');
        $itlHistory->ip_address = $this->phone->currentIpAddress();

        Log::info('DeleteItlJob@handle: Checking if phone supports ITL delete');
        if(!$this->phone->itl) {
            $itlHistory->status = 'finished';
            $itlHistory->result = 'fail';
            $itlHistory->fail_reason = 'Unsupported Model';
            $itlHistory->save();
            exit;
        }

        Log::info('DeleteItlJob@handle: Associating IP Phone with AXL User');
        $axl = new AxlSoap($this->phone->ucm);
        $associated = $axl->associatePhoneWithAppUser($this->phone);

        if(!$associated) {
            Log::error('DeleteItlJob@handle: Problem associating IP Phone with AXL User.  Failing ITL Delete');
            $itlHistory->status = 'finished';
            $itlHistory->result = 'fail';
            $itlHistory->fail_reason = 'Could not associated AXL User';
            $itlHistory->save();
            exit;
        }

        Log::info('DeleteItlJob@handle: Associated IP Phone with AXL User successfully');

        Log::info('DeleteItlJob@handle: Calling the PhoneController@deleteItl');
        (new PhoneController($this->phone))->deleteItl($itlHistory);

    }
}
