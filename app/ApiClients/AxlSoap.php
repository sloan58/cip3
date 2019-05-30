<?php

namespace App\ApiClients;

use SoapFault;
use SoapClient;
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
            Log::error('AxlSoap@ping ({$this->ucm->name}): UCM Ping fail', [
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
        $returnedTags = [
                'name' => '',
                'description' => '',
                'model' => '',
                'devicePoolName' => ''
        ];
        
        if ($this->chunk) {
            $returnedTags['skip'] = $this->skip;
            $returnedTags['first'] = $this->suggestedRows;
        }
        
        try {
            $res = $this->listPhone([
                    'searchCriteria' => [
                        'name' => '%'
                    ],
                    'returnedTags' => $returnedTags
                ]
            );

            // Process the AXL response data
            // If the top response element is an array, we have multiple records
            if (isset($res->return->phone)) {
                $iterate = is_array($res->return->phone) ? $res->return->phone : [$res->return->phone ];
                foreach ($iterate as $item) {
                    Log::info("AxlSoap:@syncPhones ({$this->ucm->name}): Processing item", [$item->name]);
                    Phone::firstOrCreate(
                        [
                            'uuid' => preg_replace('/[{}]/', '', $item->uuid),
                            'ucm_id' => $this->ucm->id
                        ],
                        [
                            'name' => $item->name,
                            'description' => $item->description,
                            'model' => $item->model,
                            'device_pool' => $item->devicePoolName->_
                        ]
                    );
                }
            }

            return true;

        } catch (SoapFault $e) {
            // If we received a throttle notification, chunk our queries
            if (preg_match('/Query request too large/', $e->faultstring)) {

                Log::error("AxlSoap:@syncPhones ({$this->ucm->name}): Received throttle notification from AXL");

                // Get the Total Rows matched and the Suggested Rows from the Error Response
                preg_match_all('/[0-9]+/', $e->faultstring, $matches);

                // Set chunk to true
                $this->chunk = true;

                // Total matched rows
                $this->totalRows = $matches[0][0];
                Log::info("AxlSoap:@syncPhones ({$this->ucm->name}): Total matches is $this->totalRows");

                // Suggested maximum rows per query
                $this->suggestedRows = floor($matches[0][1] / 10);
                Log::info("AxlSoap:@syncPhones ({$this->ucm->name}): Suggested rows is {$matches[0][1]}");
                Log::info("AxlSoap:@syncPhones ({$this->ucm->name}): Setting limit to $this->suggestedRows (1/10th) " .
                                  "to avoid a recursive throttle."
                );

                // How many iterations to get all rows
                $this->iterations =  floor($this->totalRows / $this->suggestedRows) +1;
                Log::info("AxlSoap:@syncPhones ({$this->ucm->name}): Iterations is $this->iterations");

                Log::info("AxlSoap:@syncPhones ({$this->ucm->name}): Skip set to $this->skip");
                for ($this->loop = 1; $this->loop <= $this->iterations; $this->loop++) {
                    Log::info("AxlSoap:@syncPhones ({$this->ucm->name}): Querying AXL listPhones.  " .
                                      "Iteration is $this->loop out of $this->iterations"
                    );
                    $this->syncPhones();
                    $this->skip = $this->skip + $this->suggestedRows;
                    Log::info("AxlSoap:@syncPhones ({$this->ucm->name}): Processed throttle iteration.  " .
                                      "Setting skip to $this->skip"
                    );
                }
                $this->resetThrottle();
            } else {
                Log::error("AxlSoap:@syncPhones ({$this->ucm->name}): Last AXL Request ", [$this->__getLastRequest()]);
                Log::error("AxlSoap:@syncPhones ({$this->ucm->name}): Last AXL Response ", [$this->__getLastResponse()]);

                Log::error("AxlSoap:@syncPhones ({$this->ucm->name}): AXL SOAP Exception thrown.  Tearing down sync process.");

                exit;
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
}
