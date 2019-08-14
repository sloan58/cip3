<?php

namespace App\ApiClients;

use SoapFault;
use SoapClient;
use Carbon\Carbon;
use App\Models\Ucm;
use App\Models\Phone;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class AxlSoap
 * @package App\Services
 */
class AxlSoap extends SoapClient
{
    /**
     * @var bool
     */
    private $chunk;

    /**
     * @var float
     */
    private $totalRows;

    /**
     * @var float
     */
    private $suggestedRows;

    /**
     * @var float|int
     */
    private $iterations;

    /**
     * @var int
     */
    private $skip;

    /**
     * @var int
     */
    private $loop;

    /**
     * @var Ucm
     */
    private $ucm;

    /**
     * @param Ucm $ucm
     * @throws SoapFault
     */
    public function __construct(Ucm $ucm)
    {
        $this->ucm = $ucm;

        $wsdl = storage_path('wsdl/axl/') . $this->ucm->version . '/AXLAPI.wsdl';

        $this->skip = 0;

        parent::__construct($wsdl,
            [
                'trace' => true,
                'exceptions' => true,
                'location' => "https://{$this->ucm->ip_address}:8443/axl/",
                'login' => $this->ucm->username,
                'password' => $this->ucm->password,
                'stream_context' => $this->ucm->verify_peer ?: stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]),
            ]
        );
    }


    /**
     * Make sure the AXL API is reachable
     *
     * @return bool
     */
    public function ping()
    {
        try {
            $res = $this->getCCMVersion();

            Log::info("AxlSoap@ping ({$this->ucm->name}): UCM Ping success", [
                'version' => $res->return->componentVersion->version
            ]);

            return true;

        } catch (SoapFault $e) {

            Log::error("AxlSoap@ping ({$this->ucm->name}): UCM Ping fail", [
                $e->getMessage()
            ]);

            return false;
        }
    }

    public function associatePhoneWithAppUser($phone)
    {
        Log::info("AxlSoap@associatePhoneWithAppUser ({$this->ucm->name}): Associating IP Phone {$phone->name} " .
                          "with Application User {$phone->ucm->username}");

        Log::info("AxlSoap@associatePhoneWithAppUser ({$this->ucm->name}): Getting current associated phones");
        $phones = $this->buildAssociatedPhonesArray($phone);

        Log::info("AxlSoap@associatePhoneWithAppUser ({$this->ucm->name}): Calling API to associate phones");
        try {
            $res = $this->updateAppUser([
                'userid' => $phone->ucm->username,
                'associatedDevices' => [
                    'device' => $phones
                ]
            ]);

            Log::info("AxlSoap@associatePhoneWithAppUser ({$this->ucm->name}): Received successful response");
            Log::debug("AxlSoap@associatePhoneWithAppUser ({$this->ucm->name}): Response data", [
                $res->return
            ]);

            return true;

        } catch(SoapFault $e) {
            Log::error("AxlSoap@associatePhoneWithAppUser ({$this->ucm->name}): Received unsuccessful response", [
                $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * @return bool
     * @throws GuzzleException
     */
    public function syncPhones()
    {
        Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Running syncPhones");

        $listPhoneObject = [
            'searchCriteria' => [
                'name' => '%'
            ],
            'returnedTags' => [
                'name' => '',
                'description' => '',
                'model' => '',
                'devicePoolName' => ''
            ]
        ];
        Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Set listPhoneObject ", [ $listPhoneObject ]);

        Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Checking for throttle scenario", [
            'throttle' => $this->chunk
        ]);
        if ($this->chunk) {
            $listPhoneObject['skip'] = $this->skip;
            $listPhoneObject['first'] = $this->suggestedRows;
            Log::info("AxlSoap@syncPhones ({$this->ucm->name}): We are currently throttling.  " .
                "Setting 'skip' and 'first' parameters for listPhone", [
                'skip' => $this->skip,
                'first' => $this->suggestedRows
            ]);
            Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Updated listPhoneObject ", [ $listPhoneObject ]);
        }

        Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Calling listPhone API");
        try {
            $res = $this->listPhone($listPhoneObject);
            Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Received good listPhone response");
            if (isset($res->return->phone)) {
                Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Response has interesting data");

                $iterate = is_array($res->return->phone) ? $res->return->phone : [ $res->return->phone ];

                Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Set IP Phone array to store locally");
                Log::debug("AxlSoap@syncPhones ({$this->ucm->name}): ListPhone response objects", [ $iterate ]);

                Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Calling storePhoneData method");
                $this->storePhoneData($iterate);
            }

            return true;

        } catch (SoapFault $e) {
            if (preg_match('/Query request too large/', $e->faultstring)) {

                Log::error("AxlSoap@syncPhones ({$this->ucm->name}): Received throttle notification from AXL");
                Log::debug("AxlSoap@syncPhones ({$this->ucm->name}): Last AXL Request", [
                    $this->__getLastRequest()
                ]);
                Log::debug("AxlSoap@syncPhones ({$this->ucm->name}): Last AXL Response", [
                    $this->__getLastResponse()
                ]);

                preg_match_all('/[0-9]+/', $e->faultstring, $matches);
                $this->totalRows = $matches[0][0];
                Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Total matches is $this->totalRows");

                $this->chunk = true;
                Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Setting chunk (throttle) to true");

                $this->suggestedRows = floor($matches[0][1] / 10);
                Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Suggested rows is {$matches[0][1]}");
                Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Set limit to $this->suggestedRows (1/10th) " .
                    "to avoid a recursive throttle."
                );

                $this->iterations =  floor($this->totalRows / $this->suggestedRows) +1;
                Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Iterations is $this->iterations");

                Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Skip set to $this->skip");
                for ($this->loop = 1; $this->loop <= $this->iterations; $this->loop++) {
                    Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Querying AXL listPhones.  " .
                        "Iteration is $this->loop out of $this->iterations"
                    );
                    $this->syncPhones();
                    $this->skip = $this->skip + $this->suggestedRows;
                    Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Processed throttle iteration.  " .
                        "Setting skip to $this->skip"
                    );
                }
                $this->resetThrottle();
            } else {
                Log::error("AxlSoap@syncPhones ({$this->ucm->name}): Last AXL Request ", [
                    $this->__getLastRequest()
                ]);
                Log::error("AxlSoap@syncPhones ({$this->ucm->name}): Last AXL Response ", [
                    $this->__getLastResponse()
                ]);

                Log::error("AxlSoap@syncPhones ({$this->ucm->name}): AXL SOAP Exception thrown.  " .
                    "Tearing down sync process.", [
                    'fault code' => $e->getCode(),
                    'fault message' => $e->getMessage()
                ]);

                $this->ucm->updateSyncHistory(
                    'Failed',
                    Carbon::now()->timestamp,
                    $e->getCode(),
                    $e->getMessage()
                );
                $this->ucm->sync_in_progress = false;
                $this->ucm->save();


                if(setting('teams_enable_notifications')) {

                    Log::info(
                        "AxlSoap@syncPhones ({$this->ucm->name}): Webex Teams notifications enabled.  Sending error message now."
                    );

                    $message = "{$this->ucm->name} just finished syncing with **errors**:\n\n" .
                        "> Error Code: {$e->getCode()}\n\n" .
                        "> Error Message: {$e->getMessage()}";

                    $this->ucm->sendWebexTeamsNotification($message);
                }
                exit(1);
            }
        }

        return true;
    }

    /**
     * After processing a throttle event
     * reset the throttle trackers.
     */
    private function resetThrottle()
    {
        $this->skip = 0;
        $this->loop = 0;
        $this->chunk = false;
        $this->totalRows = 0;
        $this->iterations = 0;
    }

    /**
     * @param array $iterate
     */
    protected function storePhoneData(array $iterate): void
    {
        Log::debug("AxlSoap@storePhoneData ({$this->ucm->name}): Iterating items for local storage");
        foreach ($iterate as $item) {
            Log::debug("AxlSoap@storePhoneData ({$this->ucm->name}): Processing item", [$item->name]);
            $phone = Phone::firstOrNew(
                [
                    'name' => $item->name,
                    'ucm_id' => $this->ucm->id
                ]
            );

            $phone->description = $item->description;
            $phone->model= $item->model;
            $phone->device_pool= $item->devicePoolName->_;
            $phone->save();

            Log::debug("AxlSoap@storePhoneData ({$this->ucm->name}): Stored Item", [
                'phoneId' => $phone->id
            ]);
        }
        Log::debug("AxlSoap@storePhoneData ({$this->ucm->name}): Iterating items completed.  Done!");
    }

    public function supportsBackgroundApi(Phone $phone)
    {
        Log::info("AxlSoap@supportsBackgroundApi ({$this->ucm->name}): Checking if device settings allow the " .
                          "remote operation to set background images");
        try {
            $res = $this->getPhone([
                'name' => $phone->name,
                'returnedTags' => [
                    'loadInformation' => '',
                    'phoneSuite' => ''
                ]
            ]);

            Log::info("AxlSoap@supportsBackgroundApi ({$this->ucm->name}): Received successful AXL Response", [
                $res->return->phone ?? null
            ]);

            $loadInfo = $res->return->phone->loadInformation->_;
            $phonePersonalization = $res->return->phone->phoneSuite;

            Log::info("AxlSoap@supportsBackgroundApi ({$this->ucm->name}): Found Load and Phone Personalization settings ", [
                $loadInfo, $phonePersonalization
            ]);

            Log::info("AxlSoap@supportsBackgroundApi ({$this->ucm->name}): Checking firmware version");
            $segments = preg_split('/\s|\.|\-/', $loadInfo);

            if($segments[1] < 9 || ($segments[1] == 9 && $segments[2] < 1)) {
                Log::error("AxlSoap@supportsBackgroundApi ({$this->ucm->name}): Phone firmware is not supported for this action");
                return [
                    'success' => false,
                    'reason' => 'Firmware version unsupported'
                ];
            }
            Log::info("AxlSoap@supportsBackgroundApi ({$this->ucm->name}): Firmware version is supported");


            Log::info("AxlSoap@supportsBackgroundApi ({$this->ucm->name}): Checking phone personalization setting");
            if($phonePersonalization != "Enabled") {
                Log::error("AxlSoap@supportsBackgroundApi ({$this->ucm->name}): Phone personalization is not enabled");
                return [
                    'success' => false,
                    'reason' => 'Phone personalization disabled'
                ];
            }

            Log::info("AxlSoap@supportsBackgroundApi ({$this->ucm->name}): Load and Phone Personalization settings pass checks.");
            return [
                'success' => true
            ];

        } catch (\SoapFault $e) {
            Log::error("PushBackgroundImage ({$this->phone->name}): Received AXL Soap Fault", [$e->faultstring, $e->getMessage()]);
            return [
                'success' => false,
                'reason' => 'AXL API error (Check logs)'
            ];
        }
    }

    /**
     * @param $phone
     * @return array
     */
    private function buildAssociatedPhonesArray($phone)
    {
        Log::info("AxlSoap@buildAssociatedPhonesArray ({$this->ucm->name}): Building Phone array");
        $phoneArray = [$phone->name];

        try {
            $res = $this->getAppUser([
                'userid' => $phone->ucm->username,
            ]);

            Log::info("AxlSoap@buildAssociatedPhonesArray ({$this->ucm->name}): Received successful response");
            Log::debug("AxlSoap@buildAssociatedPhonesArray ({$this->ucm->name}): Response data", [
                $res->return
            ]);

            if (!isset($res->return->appUser->associatedDevices->device)) {
                Log::info("AxlSoap@buildAssociatedPhonesArray ({$this->ucm->name}): No Phones currently associated" .
                                   " to this user.  Returning the input Phone $phone->name");
                return $phoneArray;
            }

            Log::info("AxlSoap@buildAssociatedPhonesArray ({$this->ucm->name}): Merging associated Phones and" .
                              " new phone $phone->name", [$res->return->appUser->associatedDevices->device]);
            $phoneArray = array_merge($phoneArray, (array) $res->return->appUser->associatedDevices->device);

            Log::info("AxlSoap@buildAssociatedPhonesArray ({$this->ucm->name}): Removing duplicate Phone names");
            $phoneArray = array_unique($phoneArray);

            Log::info("AxlSoap@buildAssociatedPhonesArray ({$this->ucm->name}): Returning Phone array", [$phoneArray]);
            return $phoneArray;

        } catch(SoapFault $e) {
            Log::error("AxlSoap@buildAssociatedPhonesArray ({$this->ucm->name}): Received unsuccessful response", [
                $e->getMessage()
            ]);
            return [$phone->name];
        }
    }
}
