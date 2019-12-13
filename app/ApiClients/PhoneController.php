<?php


namespace App\ApiClients;

use App\Models\Phone;
use Sabre\Xml\Reader;
use GuzzleHttp\Client;
use App\Models\ItlHistory;
use App\Models\BgImageHistory;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class PhoneController
{
    /**
     * @var Phone
     */
    private $phone;

    /**
     * Has a request already timed out once before
     */
    private $hasAlreadyTimedOut;

    /**
     * PhoneController constructor.
     * @param Phone $phone
     */
    public function __construct(Phone $phone)
    {
        $this->client = new Client([
            'base_uri' => 'http://' . $phone->currentIpAddress(),
            'verify' => false,
            'timeout' => 10,
            'connect_timeout' => 10,
            'headers' => [
                'Accept' => 'application/xml',
                'Content-Type' => 'application/xml'
            ],
            'auth' => [
                $phone->ucm->username, $phone->ucm->password
            ],
        ]);

        $this->hasAlreadyTimedOut = false;

        $this->phone = $phone;

        $this->reader = new Reader;
    }

    public function deleteItl(ItlHistory $itlHistory)
    {
        Log::info('PhoneController@deleteItl: Starting deleteItl process.');
        Log::info("PhoneController@deleteItl: Iterating keys for phone model {$this->phone->model}", [
            $this->phone->itl->sequence
        ]);
        foreach ($this->phone->itl->sequence as $index => $keyPress) {
            Log::debug("PhoneController@deleteItl: Processing key {$keyPress}");

            if ($keyPress == "Key:Sleep") {
                sleep(2);
                continue;
            }

            $xml = 'XML=<CiscoIPPhoneExecute><ExecuteItem Priority="0" URL="' . $keyPress . '"/></CiscoIPPhoneExecute>';
            Log::debug("PhoneController@deleteItl: Set XML Object: ", [$xml]);

            Log::debug("PhoneController@deleteItl: Calling IP Phone API");
            try {
                $response = $this->client->post('http://' . $this->phone->currentIpAddress() . '/CGI/Execute', ['body' => $xml]);

                $this->reader->xml($response->getBody()->getContents());
                $response = $this->reader->parse();

                Log::debug("PhoneController@deleteItl: Received response:", [
                    $response
                ]);

                if (isset($response['name']) &&  $response['name'] == '{}CiscoIPPhoneError') {
                    Log::error("PhoneController@deleteItl: Got an error in the IP Phone response");
                    //Log an Error
                    switch ($response['attributes']['Number']) {
                        case 4:
                            $errorType = 'Authentication Exception';
                            break;
                        case 6:
                            $errorType = 'Invalid URL Exception';
                            break;
                        default:
                            $errorType = 'Unknown Exception';
                            break;
                    }
                    Log::error("PhoneController@deleteItl: Error message: ", [$errorType]);
                    Log::error("PhoneController@deleteItl: Setting ITL Process to fail");
                    $itlHistory->fail_reason = $errorType;
                    $itlHistory->status = 'finished';
                    $itlHistory->result = 'fail';
                    $itlHistory->save();

                    return false;
                }
            } catch (RequestException $e) {
                Log::error("PhoneController@deleteItl: Got an error in the API response", [$e->getMessage()]);

                /*
                 * Handle an exception from the Guzzle client itself
                 */
                if ($e instanceof ClientException) {
                    //Unauthorized
                    $itlHistory->fail_reason = "Authentication Exception";
                } elseif ($e instanceof ConnectException) {
                    //Can't Connect
                    $itlHistory->fail_reason = "Connection Exception";
                } else {
                    //Other exception
                    $itlHistory->fail_reason = "Unknown Exception: $e->getMessage()";
                }

                Log::error("PhoneController@deleteItl: Setting ITL Process to fail.  Checking if this was the " .
                    "first issued key command");
                if ($index == 0) {
                    Log::error("PhoneController@deleteItl: Setting ITL Process to fail.  Timed out on first API request ");
                    $itlHistory->fail_reason = 'Timeout on first API request.';
                    $itlHistory->status = 'finished';
                    $itlHistory->result = 'fail';
                    $itlHistory->save();
                    return false;
                } else {
                    Log::error("PhoneController@deleteItl: Setting ITL Process to fail.  " .
                        "Timed out after successful API requests (phone might be rebooting)");
                    $itlHistory->fail_reason = 'Timeout after successful API requests (phone might be rebooting)';
                    $itlHistory->status = 'finished';
                    $itlHistory->result = 'success';
                    $itlHistory->save();
                    return true;
                }
            }
        }

        Log::info("PhoneController@deleteItl: ITL Delete completed successfully");
        $itlHistory->status = 'finished';
        $itlHistory->result = 'success';
        $itlHistory->save();

        return true;
    }

    public function pushBackgroundImage(BgImageHistory $bgImageHistory)
    {
        Log::info('PhoneController@pushBackgroundImage: Starting pushBackgroundImage process');

        $fullImage = env('APP_URL') . "/storage/backgrounds/{$this->phone->getFullSizeBgDimensions()}/$bgImageHistory->image";
        $thumbImage = env('APP_URL') . "/storage/backgrounds/{$this->phone->getFullSizeBgDimensions()}/" . basename($bgImageHistory->image, '.png') . "_thumb.png";

        $xml = "XML=<setBackground><background><image>$fullImage</image><icon>$thumbImage</icon></background></setBackground>";
        Log::info('PhoneController@pushBackgroundImage: Set XML body', [
            $xml
        ]);

        Log::info('PhoneController@pushBackgroundImage: Sending HTTP request to phone');
        try {
            $response = $this->client->post('CGI/Execute', ['body' => $xml]);

            Log::info("PhoneController@pushBackgroundImage: Received Guzzle HTTP Response from IP Phone - ", [
                $response->getReasonPhrase(),
                $xml
            ]);

            $bgImageHistory->status = 'finished';
            $bgImageHistory->result = 'success';
            $bgImageHistory->save();

            $this->savePhoneScreenShot($bgImageHistory);

            Log::info('PhoneController@pushBackgroundImage: Push Background Image completed successfully!');
            return true;
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                Log::error("PhoneController@pushBackgroundImage: Received Guzzle HTTP Client Error - ", [
                    'Response' => $e->getResponse()->getBody(),
                    'Request' => $e->getRequest()->getBody()
                ]);
                $bgImageHistory->fail_reason = $e->getResponse()->getBody();
            } else {
                if ($this->hasAlreadyTimedOut) {
                    Log::error("PhoneController@pushBackgroundImage: Received second Guzzle HTTP Client Timeout.");
                    Log::error("PhoneController@pushBackgroundImage: Considering this try a fail.  Storing results.");
                    $bgImageHistory->fail_reason = 'timeout';
                } else {
                    Log::error("PhoneController@pushBackgroundImage: This is the first timeout in the request series.  We'll try it once more.");
                    $this->hasAlreadyTimedOut = true;
                    Log::error("PhoneController@pushBackgroundImage: Set hasAlreadyTimedOut to $this->hasAlreadyTimedOut.");
                    $this->pushBackgroundImage($bgImageHistory);
                }
            }
            $bgImageHistory->status = 'finished';
            $bgImageHistory->result = 'fail';
            $bgImageHistory->save();
            return false;
        }
    }

    /**
     * Save a copy of the IP Phone screen shot after pushing a new image
     * @param BgImageHistory $bgImageHistory
     */
    private function savePhoneScreenShot(BgImageHistory $bgImageHistory)
    {
        sleep(5);
        Log::info('PhoneController@savePhoneScreenShot: Saving screen shot of the IP Phone');
        try {
            $response = $this->client->get('CGI/Screenshot', [
                'sink' => storage_path("app/public/screenshots/{$bgImageHistory->id}_{$this->phone->name}.png")
            ]);

            Log::info("PhoneController@savePhoneScreenShot: Received Guzzle HTTP Response from IP Phone - ", [
                $response->getReasonPhrase()
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                Log::error("PhoneController@savePhoneScreenShot: Received Guzzle HTTP Client Error - ", [
                    'Response' => $e->getResponse()->getBody(),
                    'Request' => $e->getRequest()->getBody()
                ]);
            }
        }
    }
}
