<?php

namespace App\Jobs;

use App\ApiClients\AxlSoap;
use SoapFault;
use App\Models\Phone;
use App\Models\Eraser;
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
     * Create a new job instance.
     *
     * @param Phone $phone
     */
    public function __construct(Phone $phone)
    {
        $this->phone = $phone;
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

        Log::info('DeleteItlJob@handle: Creating Eraser object');
        $eraser = new Eraser;
        $eraser->phone = $this->phone->name;

        Log::info('DeleteItlJob@handle: Pulling latest realtime information for this phone');
        $ris = new RisPortSoap($this->phone->ucm, false);
        $ris->collectRealtimeData([$this->phone->name]);

        Log::info('DeleteItlJob@handle: Checking if phone is registered and has an IP Address');
        if(!$this->phone->isRegistered() || is_null($this->phone->currentIpAddress())) {
            Log::info('DeleteItlJob@handle: The phone is either not registered or does not have an IP address',
                ['Status' => $this->phone->status, 'IPAddress' => $this->phone->currentIpAddress()]
            );

            $eraser->status = 'finished';
            $eraser->result = 'fail';
            $eraser->fail_reason = !$this->phone->isRegistered() ? 'Phone not registered' : 'No known IP Address';
            $eraser->save();
            exit;
        }

        Log::info('DeleteItlJob@handle: Phone is registered and has an IP Address');
        $eraser->ip_address = $this->phone->currentIpAddress();

        Log::info('DeleteItlJob@handle: Checking if phone supports ITL delete');
        if(!$this->phone->itl) {
            $eraser->status = 'finished';
            $eraser->result = 'fail';
            $eraser->fail_reason = 'Unsupported Model';
            $eraser->save();
            exit;
        }

        Log::info('DeleteItlJob@handle: Associating IP Phone with AXL User');
        $axl = new AxlSoap($this->phone->ucm);
        $associated = $axl->associatePhoneWithAppUser($this->phone);

        if(!$associated) {
            Log::error('DeleteItlJob@handle: Problem associating IP Phone with AXL User.  Failing ITL Delete');
            $eraser->status = 'finished';
            $eraser->result = 'fail';
            $eraser->fail_reason = 'Could not associated AXL User';
            $eraser->save();
            exit;
        }

        Log::info('DeleteItlJob@handle: Associated IP Phone with AXL User successfully');

        Log::info('DeleteItlJob@handle: Calling the PhoneController@deleteItl');
        $phoneController = new PhoneController($this->phone, $eraser);

        $phoneController->deleteItl();

    }
}
