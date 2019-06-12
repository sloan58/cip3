<?php

namespace App\ApiClients;

use SoapFault;
use SoapClient;
use Carbon\Carbon;
use App\Models\Ucm;
use App\Models\Phone;
use Illuminate\Support\Facades\Log;

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

    /**
     * @return bool
     */
    public function syncPhones()
    {
        Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Running syncPhones");

        $returnedTags = [
                'name' => '',
                'description' => '',
                'model' => '',
                'devicePoolName' => ''
        ];
        Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Set listPhone returnedTags ", [ $returnedTags ]);

        Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Checking for throttle scenario", [
            'throttle' => $this->chunk
        ]);
        if ($this->chunk) {
            $returnedTags['skip'] = $this->skip;
            $returnedTags['first'] = $this->suggestedRows;
            Log::info("AxlSoap@syncPhones ({$this->ucm->name}): We are currently throttling.  " .
                "Setting skip and first parameters for listPhone", [
                    'skip' => $this->skip,
                    'first' => $this->suggestedRows
            ]);
        }

        Log::info("AxlSoap@syncPhones ({$this->ucm->name}): Calling listPhone API");
        try {
            $res = $this->listPhone([
                    'searchCriteria' => [
                        'name' => '%'
                    ],
                    'returnedTags' => $returnedTags
                ]
            );

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
                    false,
                    Carbon::now()->timestamp,
                    $e->getCode(),
                    $e->getMessage()
                );
                $this->ucm->sync_in_progress = false;
                $this->ucm->save();

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
            $phone = Phone::firstOrCreate(
                [
                    'name' => $item->name,
                    'ucm_id' => $this->ucm->id
                ],
                [
                    'description' => $item->description,
                    'model' => $item->model,
                    'device_pool' => $item->devicePoolName->_
                ]
            );
            Log::debug("AxlSoap@storePhoneData ({$this->ucm->name}): Stored Item", [
                'phoneId' => $phone->id
            ]);
        }
        Log::debug("AxlSoap@storePhoneData ({$this->ucm->name}): Iterating items completed.  Done!");
    }
}
