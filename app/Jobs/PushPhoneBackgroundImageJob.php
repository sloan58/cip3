<?php

namespace App\Jobs;

use SoapFault;
use App\Models\Phone;
use App\ApiClients\AxlSoap;
use Illuminate\Bus\Queueable;
use App\ApiClients\RisPortSoap;
use App\Models\RemoteOperation;
use App\ApiClients\PhoneController;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PushPhoneBackgroundImageJob implements ShouldQueue
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
     * The image file to push to the phone
     */
    private $image;

    /**
     * Create a new job instance.
     *
     * @param Phone $phone
     * @param $requestedBy
     * @param $image
     */
    public function __construct(Phone $phone, $requestedBy, $image)
    {
        $this->phone = $phone;
        $this->requestedBy = $requestedBy;
        $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws GuzzleException
     * @throws SoapFault
     */
    public function handle()
    {
        Log::info(
            'PushPhoneBackgroundImageJob@handle: Starting Push Phone Background Image job for device',
            [$this->phone->name]
        );

        Log::info('PushPhoneBackgroundImageJob@handle: Creating RemoteOperation object');
        $remoteOperation = new RemoteOperation;
        $remoteOperation->type = 'background-push';
        $remoteOperation->phone = $this->phone->name;
        $remoteOperation->requested_by = $this->requestedBy;

        Log::info('PushPhoneBackgroundImageJob@handle: Pulling latest realtime information for this phone');
        $ris = new RisPortSoap($this->phone->ucm, false);
        $ris->collectRealtimeData([$this->phone->name]);

        Log::info('PushPhoneBackgroundImageJob@handle: Checking if phone is registered and has an IP Address');
        if(!$this->phone->isRegistered() || is_null($this->phone->currentIpAddress())) {
            Log::info('PushPhoneBackgroundImageJob@handle: The phone is either not registered or does not have an IP address',
                ['Status' => $this->phone->status, 'IPAddress' => $this->phone->currentIpAddress()]
            );

            $remoteOperation->status = 'finished';
            $remoteOperation->result = 'fail';
            $remoteOperation->fail_reason = !$this->phone->isRegistered() ? 'Phone not registered' : 'No known IP Address';
            $remoteOperation->save();
            exit;
        }

        Log::info('PushPhoneBackgroundImageJob@handle: Phone is registered and has an IP Address');
        $remoteOperation->ip_address = $this->phone->currentIpAddress();

        Log::info('PushPhoneBackgroundImageJob@handle: Checking if phone supports background image push');
        $axl = new AxlSoap($this->phone->ucm);
        $supportsBgPush = $axl->supportsBackgroundApi($this->phone);
        if(!$supportsBgPush['success']) {
            $remoteOperation->status = 'finished';
            $remoteOperation->result = 'fail';
            $remoteOperation->fail_reason = $supportsBgPush['reason'];
            $remoteOperation->save();
            exit;
        }

        Log::info('PushPhoneBackgroundImageJob@handle: Background image push is supported!');

        Log::info('PushPhoneBackgroundImageJob@handle: Associating IP Phone with AXL User');
        $associated = $axl->associatePhoneWithAppUser($this->phone);

        if(!$associated) {
            Log::error('PushPhoneBackgroundImageJob@handle: Problem associating IP Phone with AXL User.  Failing ITL Delete');
            $remoteOperation->status = 'finished';
            $remoteOperation->result = 'fail';
            $remoteOperation->fail_reason = 'Could not associated AXL User';
            $remoteOperation->save();
            exit;
        }

        Log::info('PushPhoneBackgroundImageJob@handle: Associated IP Phone with AXL User successfully');

        Log::info('PushPhoneBackgroundImageJob@handle: Calling the PhoneController@pushBackgroundImage');
        (new PhoneController($this->phone, $remoteOperation))->pushBackgroundImage($this->image);
    }
}
