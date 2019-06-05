<?php


namespace App\ApiClients;

use Exception;
use SoapFault;
use SoapClient;
use Carbon\Carbon;
use App\Models\Ucm;
use App\Models\Phone;
use Illuminate\Support\Facades\Log;

class RisPortSoap extends SoapClient
{
    /**
     * @var Ucm
     */
    private $ucm;

    /**
     * @var array
     */
    private $phones;

    /**
     * @param Ucm $ucm
     * @throws SoapFault
     */
    public function __construct(Ucm $ucm)
    {
        $this->ucm = $ucm;

        $wsdl = "https://{$this->ucm->ip_address}:8443/realtimeservice2/services/RISService70?wsdl";

        parent::__construct($wsdl,
            [
                'trace' => true,
                'exceptions' => true,
                'location' => "https://{$this->ucm->ip_address}:8443/realtimeservice2/services/RISService70",
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

        $this->phones = [];
    }

    /**
     *  Accept a single Phone name or array of phone names
     *  and create the RisPort SelectItems object
     *
     * @param $phones
     * @return void
     * @throws SoapFault
     */
    public function collectRealtimeData($phones)
    {
        Log::info("RisPortSoap@setRisPhoneArray ({$this->ucm->name}): Collecting Realtime Data");

        Log::info("RisPortSoap@setRisPhoneArray ({$this->ucm->name}): Building RisPort Item array");
        foreach ((array) $phones as $phone)
        {
            array_push($this->phones, ['Item' => $phone]);
        }
        Log::info("RisPortSoap@setRisPhoneArray ({$this->ucm->name}): Set RisPhoneArray - ", [
            count($this->phones)
        ]);

        Log::info("RisPortSoap@setRisPhoneArray ({$this->ucm->name}): Calling RisPort API in 1k chunks");
        foreach(array_chunk($this->phones, 1000) as $chunk) {
            $this->queryRisPort($chunk);
        }
    }

    /**
     * Query the UCM RisPort API for Realtime Information
     *
     * @param $phones
     * @return bool|Exception|SoapFault
     * @throws SoapFault
     */
    private function queryRisPort($phones)
    {
        Log::info("RisPortSoap@queryRisPort: ({$this->ucm->name}) Sending SelectCmDeviceExt request to UCM");
        try {
            $response = $this->SelectCmDeviceExt([
                'StateInfo' => '',
                'CmSelectionCriteria' => [
                    'MaxReturnedDevices'=>'1000',
                    'DeviceClass'=>'Phone',
                    'Model'=>'255',
                    'Status'=>'Any',
                    'NodeName'=>'',
                    'SelectBy'=>'Name',
                    'SelectItems'=>
                        $phones
                ]]);

            Log::info("RisPortSoap@queryRisPort: ({$this->ucm->name}) Received successful response");
            $realtimeData = $response->selectCmDeviceReturn->SelectCmDeviceResult->CmNodes->item->CmDevices->item;
            dump($response);
            $this->storeRealtimeData($realtimeData);
            return true;

        } catch (SoapFault $e) {
            Log::error("RisPortSoap@queryRisPort: ({$this->ucm->name}) Received Error Response", [
                [ 'faultstring' => $e->faultstring ],
                [ 'message' => $e->getMessage() ],
                [ 'last request' => $this->__getLastRequest() ],
                [ 'last response' => $this->__getLastResponse() ],
                [ 'last request headers' => $this->__getLastRequestHeaders() ]
            ]);

            // The typo in the error message below is intended.  It's what gets sent in the response from UCM :-)
            if (preg_match('/^AxisFault: Exceeded allowed rate for Reatime information/',$e->faultstring))
            {
                Log::error("RisPortSoap@queryRisPort: ({$this->ucm->name}) Error was a throttle response.  " .
                                   "Sleeping 30 seconds"
                );
                sleep(30);
                $this->queryRisPort($phones);
            }
            Log::error("RisPortSoap@queryRisPort: ({$this->ucm->name}) Error was not a throttle response.  Exiting");
            exit(1);
        }
    }

    /**
     * Store the RisPort Realtime Data for each Phone
     *
     * @param $realtimeData
     */
    private function storeRealtimeData($realtimeData)
    {
        Log::info("RisPortSoap@storeRealtimeData: ({$this->ucm->name}) Storing Realtime Data locally");
        foreach($realtimeData as $data) {

            Log::debug("RisPortSoap@storeRealtimeData: ({$this->ucm->name}) Processing item", [ $data ]);

            $phone = Phone::where([
                'name' => $data->Name,
                'ucm_id' => $this->ucm->id
            ])->first();
            Log::debug("RisPortSoap@storeRealtimeData: ({$this->ucm->name}) Updating IP Phone ", [
                'phoneId' => $phone->id
            ]);

            $lines = array_map(function($line) {
                return $line->DirectoryNumber;
            }, is_array($data->LinesStatus->item) ? $data->LinesStatus->item : [$data->LinesStatus->item]);

            $currentStatus = [
                'Status' => $data->Status,
                'StatusReason' => $data->StatusReason,
                'Protocol' => $data->Protocol,
                'NumOfLines' => $data->NumOfLines,
                'Lines' => $lines,
                'ActiveLoadID' => $data->ActiveLoadID,
                'InactiveLoadID' => $data->InactiveLoadID,
                'DownloadStatus' => $data->DownloadStatus,
                'DownloadFailureReason' => $data->DownloadFailureReason,
                'IPAddress' => $data->IPAddress->item->IP,
                'UCMTimestamp' => $data->TimeStamp,
                'CIP3Timestamp' => Carbon::now()->timestamp
            ];

            dump($currentStatus);
            Log::debug("RisPortSoap@storeRealtimeData: ({$this->ucm->name}) Setting current status ", [
                'currentStatus' => $currentStatus
            ]);

            if($phone->realtime_data) {
                $statuses = $phone->realtime_data;
                Log::debug("RisPortSoap@storeRealtimeData: ({$this->ucm->name}) Adding new status to DB ", [
                    'statuses_before' => $statuses,

                ]);
                array_unshift($statuses, $currentStatus);
                $phone->realtime_data = $statuses;
                Log::debug("RisPortSoap@storeRealtimeData: ({$this->ucm->name}) Adding new status to DB ", [
                    'statuses_after' => $statuses,

                ]);
            } else {
                Log::debug("RisPortSoap@storeRealtimeData: ({$this->ucm->name}) DB status is empty.  " .
                                   "Adding current status"
                );
                $phone->realtime_data = [$currentStatus];
            }

            Log::debug("RisPortSoap@storeRealtimeData: ({$this->ucm->name}) Storing phone data");
            $phone->save();
        }
        Log::info("RisPortSoap@storeRealtimeData: ({$this->ucm->name}) Storing Realtime Data complete");
    }
}
