<?php


namespace App\ApiClients;


use App\Models\Phone;
use Sabre\Xml\Reader;
use GuzzleHttp\Client;
use App\Models\Eraser;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class PhoneController
{
    /**
     * @var Eraser
     */
    private $eraser;
    /**
     * @var Phone
     */
    private $phone;

    /**
     * PhoneController constructor.
     * @param Phone $phone
     * @param Eraser $eraser
     */
    function __construct(Phone $phone, Eraser $eraser)
    {

        $this->client = new Client([
            'base_uri' => 'http://' . $phone->currentIpAddress(),
            'verify' => false,
            'timeout' => 10,
            'connect_timeout' => 2,
            'headers' => [
                'Accept' => 'application/xml',
                'Content-Type' => 'application/xml'
            ],
            'auth' => [
                $phone->ucm->username, $phone->ucm->password
            ],
        ]);

        $this->eraser = $eraser;
        $this->phone = $phone;

        $this->reader = new Reader;
    }

    public function deleteItl()
    {
        Log::info('PhoneController@deleteItl: Starting deleteItl process.');
        Log::info("PhoneController@deleteItl: Iterating keys for phone model {$this->phone->model}", [
            $this->phone->itl->sequence
        ]);
        foreach($this->phone->itl->sequence as $index => $keyPress) {

            Log::debug("PhoneController@deleteItl: Processing key {$keyPress}");

            if ($keyPress == "Key:Sleep")
            {
                sleep(2);
                continue;
            }

            $xml = 'XML=<CiscoIPPhoneExecute><ExecuteItem Priority="0" URL="' . $keyPress . '"/></CiscoIPPhoneExecute>';
            Log::debug("PhoneController@deleteItl: Set XML Object: ", [ $xml ]);

            Log::debug("PhoneController@deleteItl: Calling IP Phone API");
            try {

                $response = $this->client->post('http://' . $this->phone->currentIpAddress() . '/CGI/Execute',['body' => $xml]);

                $this->reader->xml($response->getBody()->getContents());
                $response = $this->reader->parse();

                Log::debug("PhoneController@deleteItl: Received response:", [
                    $response
                ]);

                if(isset($response['name']) &&  $response['name'] == '{}CiscoIPPhoneError')
                {
                    Log::error("PhoneController@deleteItl: Got an error in the IP Phone response");
                    //Log an Error
                    switch($response['attributes']['Number'])
                    {
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
                    $this->eraser->fail_reason = $errorType;
                    $this->eraser->status = 'finished';
                    $this->eraser->result = 'fail';
                    $this->eraser->save();

                    return false;
                }

            } catch (RequestException $e) {

                Log::error("PhoneController@deleteItl: Got an error in the API response", [$e->getMessage()]);

                /*
                 * Handle an exception from the Guzzle client itself
                 */
                if($e instanceof ClientException)
                {
                    //Unauthorized
                    $this->eraser->fail_reason = "Authentication Exception";

                }
                elseif($e instanceof ConnectException)
                {
                    //Can't Connect
                    $this->eraser->fail_reason = "Connection Exception";
                }
                else
                {
                    //Other exception
                    $this->eraser->fail_reason = "Unknown Exception: $e->getMessage()";
                }

                Log::error("PhoneController@deleteItl: Setting ITL Process to fail.  Checking if this was the " .
                                    "first issued key command");
                if($index == 0) {
                    $this->eraser->status = 'finished';
                    $this->eraser->result = 'fail';
                    $this->eraser->save();
                    return false;
                } else {
                    $this->eraser->fail_reason = 'Note: timed out after first key.';
                    $this->eraser->status = 'finished';
                    $this->eraser->result = 'success';
                    $this->eraser->save();
                    return true;
                }
            }
        }

        Log::info("PhoneController@deleteItl: ITL Delete completed successfully");
        $this->eraser->status = 'finished';
        $this->eraser->result = 'success';
        $this->eraser->save();

        return true;
    }
}
