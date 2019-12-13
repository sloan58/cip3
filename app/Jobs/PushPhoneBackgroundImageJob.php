<?php

namespace App\Jobs;

use App\Models\BgImageHistory;
use SoapFault;
use App\Models\Phone;
use App\ApiClients\AxlSoap;
use Illuminate\Bus\Queueable;
use App\ApiClients\RisPortSoap;
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
     * Fetch current RISPort data for this phone
     */
    private $refreshRealtimeData;

    /**
     * Associate the phone to the application user
     */
    private $associatePhone;

    /**
     * Create a new job instance.
     *
     * @param Phone $phone
     * @param $requestedBy
     * @param $image
     */
    public function __construct(Phone $phone, $requestedBy, $image, $refreshRealtimeData = false, $associatePhone = false)
    {
        $this->phone = $phone;
        $this->requestedBy = $requestedBy;
        $this->image = $image;
        $this->refreshRealtimeData = $refreshRealtimeData;
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

        Log::info('PushPhoneBackgroundImageJob@handle: Creating BgImageHistory object');
        $bgImageHistory = new BgImageHistory;
        $bgImageHistory->image = $this->image;
        $bgImageHistory->phone = $this->phone->name;
        $bgImageHistory->requested_by = $this->requestedBy;

        if ($this->refreshRealtimeData) {
            Log::info('PushPhoneBackgroundImageJob@handle: Pulling latest realtime information for this phone');
            $ris = new RisPortSoap($this->phone->ucm, false);
            $ris->collectRealtimeData([$this->phone->name]);
        }

        Log::info('PushPhoneBackgroundImageJob@handle: Checking if phone is registered and has an IP Address');
        if (!$this->phone->isRegistered() || is_null($this->phone->currentIpAddress())) {
            Log::info(
                'PushPhoneBackgroundImageJob@handle: The phone is either not registered or does not have an IP address',
                ['Status' => $this->phone->status, 'IPAddress' => $this->phone->currentIpAddress()]
            );

            $bgImageHistory->status = 'finished';
            $bgImageHistory->result = 'fail';
            $bgImageHistory->fail_reason = !$this->phone->isRegistered() ? 'Phone not registered' : 'No known IP Address';
            $bgImageHistory->save();
            exit;
        }

        Log::info('PushPhoneBackgroundImageJob@handle: Phone is registered and has an IP Address');
        $bgImageHistory->ip_address = $this->phone->currentIpAddress();

        Log::info('PushPhoneBackgroundImageJob@handle: Checking if phone supports background image push');
        $axl = new AxlSoap($this->phone->ucm);
        $supportsBgPush = $axl->supportsBackgroundApi($this->phone);
        if (!$supportsBgPush['success']) {
            $bgImageHistory->status = 'finished';
            $bgImageHistory->result = 'fail';
            $bgImageHistory->fail_reason = $supportsBgPush['reason'];
            $bgImageHistory->save();
            exit;
        }

        Log::info('PushPhoneBackgroundImageJob@handle: Background image push is supported!');

        if ($associatePhone) {
            Log::info('PushPhoneBackgroundImageJob@handle: Associating IP Phone with AXL User');
            $associated = $axl->associatePhoneWithAppUser($this->phone);
    
            if (!$associated) {
                Log::error('PushPhoneBackgroundImageJob@handle: Problem associating IP Phone with AXL User.  Failing ITL Delete');
                $bgImageHistory->status = 'finished';
                $bgImageHistory->result = 'fail';
                $bgImageHistory->fail_reason = 'Could not associated AXL User';
                $bgImageHistory->save();
                exit;
            }
        }

        Log::info('PushPhoneBackgroundImageJob@handle: Associated IP Phone with AXL User successfully');

        Log::info('PushPhoneBackgroundImageJob@handle: Calling the PhoneController@pushBackgroundImage');
        (new PhoneController($this->phone))->pushBackgroundImage($bgImageHistory);
    }
}
