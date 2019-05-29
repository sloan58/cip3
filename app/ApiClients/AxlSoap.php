<?php

namespace App\ApiClients;

use SoapFault;
use SoapClient;
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
     * @var string
     */
    private $ip;

    /**
     * @param $version
     * @param $ip
     * @param $user
     * @param $password
     * @param bool $verifyPeer
     * @throws SoapFault
     */
    public function __construct($version, $ip, $user, $password, $verifyPeer = true)
    {
        $this->ip = $ip;
        
        $wsdl = storage_path('wsdl/axl/') . $version . '/AXLAPI.wsdl';

        parent::__construct($wsdl,
            [
                'trace' => true,
                'exceptions' => true,
                'location' => "https://{$ip}:8443/axl/",
                'login' => $user,
                'password' => $password,
                'stream_context' => $verifyPeer ?: stream_context_create([
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

            Log::info("AxlSoap@ping ($this->ip): UCM Ping success", [
                'version' => $res->return->componentVersion->version
            ]);
            return true;

        } catch (SoapFault $e) {
            Log::error('AxlSoap@ping ($this->ip): UCM Ping fail', [
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
            dump($res);

            // Process the AXL response data
            // If the top response element is an array, we have multiple records
            if (isset($res->return->phone)) {
                $iterate = is_array($res->return->phone) ? $res->return->phone : [$res->return->phone ];
                foreach ($iterate as $item) {
                    Log::info("AxlSoap:@syncPhones ($this->ip): Processing item", [$item->name]);
                }
            }

            return true;

        } catch (SoapFault $e) {
            // If we received a throttle notification, chunk our queries
            if (preg_match('/Query request too large/', $e->faultstring)) {

                Log::error("AxlSoap:@syncPhones ($this->ip): Received throttle notification from AXL");

                // Get the Total Rows matched and the Suggested Rows from the Error Response
                preg_match_all('/[0-9]+/', $e->faultstring, $matches);

                // Set chunk to true
                $this->chunk = true;

                // Total matched rows
                $this->totalRows = $matches[0][0];
                Log::info("AxlSoap:@syncPhones ($this->ip): Total matches is $this->totalRows");

                // Suggested maximum rows per query
                $this->suggestedRows = floor($matches[0][1] / 10);
                Log::info("AxlSoap:@syncPhones ($this->ip): Suggested rows is {$matches[0][1]}");
                Log::info("AxlSoap:@syncPhones ($this->ip): Setting limit to $this->suggestedRows (1/10th) " .
                                  "to avoid a recursive throttle."
                );

                // How many iterations to get all rows
                $this->iterations =  floor($this->totalRows / $this->suggestedRows) +1;
                Log::info("AxlSoap:@syncPhones ($this->ip): Iterations is $this->iterations");

                Log::info("AxlSoap:@syncPhones ($this->ip): Skip set to $this->skip");
                for ($this->loop = 1; $this->loop <= $this->iterations; $this->loop++) {
                    Log::info("AxlSoap:@syncPhones ($this->ip): Querying AXL listPhones.  " .
                                      "Iteration is $this->loop out of $this->iterations"
                    );
                    $this->syncPhones();
                    $this->skip = $this->skip + $this->suggestedRows;
                    Log::info("AxlSoap:@syncPhones ($this->ip): Processed throttle iteration.  " .
                                      "Setting skip to $this->skip"
                    );
                }
                $this->resetThrottle();
            } else {
                Log::error("AxlSoap:@syncPhones ($this->ip): Last AXL Request ", [$this->__getLastRequest()]);
                Log::error("AxlSoap:@syncPhones ($this->ip): Last AXL Response ", [$this->__getLastResponse()]);

                Log::error("AxlSoap:@syncPhones ($this->ip): AXL SOAP Exception thrown.  Tearing down sync process.");

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
