<?php


namespace App\ApiClients;

use Exception;
use SoapFault;
use SoapClient;
use Carbon\Carbon;
use App\Models\Ucm;
use App\Models\Phone;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;

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
     * @var bool
     */
    private $isFullSync;

    /**
     * @param Ucm $ucm
     * @param bool $isFullSync
     * @throws SoapFault
     */
    public function __construct(Ucm $ucm, $isFullSync = true)
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
        $this->isFullSync = $isFullSync;
    }

    /**
     *  Accept a single Phone name or array of phone names
     *  and create the RisPort SelectItems object
     *
     * @param $phones
     * @return void
     * @throws GuzzleException
     * @throws SoapFault
     */
    public function collectRealtimeData($phones)
    {
        Log::info("RisPortSoap@collectRealtimeData ({$this->ucm->name}): Collecting Realtime Data");

        $this->buildRisPortPhoneArray($phones);

        $totalIterations = floor(count($this->phones) / 1000);
        Log::info("RisPortSoap@collectRealtimeData ({$this->ucm->name}): Total iterations is $totalIterations");

        Log::info("RisPortSoap@collectRealtimeData ({$this->ucm->name}): Calling RisPort API in 1k chunks");
        foreach(array_chunk($this->phones, 1000) as $loop => $chunk) {
            Log::info(
                "RisPortSoap@collectRealtimeData ({$this->ucm->name}): Processing loop " . ($loop + 1) . " of $totalIterations"
            );
            $realtimeData = $this->queryRisPort($chunk);
            if($realtimeData) {
                $this->storeRealtimeData($realtimeData);
            }
        }
    }

    /**
     * Query the UCM RisPort API for Realtime Information
     *
     * @param $phones
     * @return bool|Exception|SoapFault
     * @throws SoapFault
     * @throws GuzzleException
     */
    private function queryRisPort($phones)
    {
        Log::info("RisPortSoap@queryRisPort ({$this->ucm->name}): Sending SelectCmDeviceExt request to UCM");
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

            Log::info("RisPortSoap@queryRisPort ({$this->ucm->name}): Received successful response");

            Log::info("RisPortSoap@queryRisPort ({$this->ucm->name}): " .
                              "Checking to see if there is any RisPort data available for devices"
            );

            if(!$response->selectCmDeviceReturn->SelectCmDeviceResult->TotalDevicesFound)
            {
                Log::info("RisPortSoap@queryRisPort ({$this->ucm->name}): No device data available to process.");
                return false;
            }

            Log::info("RisPortSoap@queryRisPort ({$this->ucm->name}): Device data is available.");
            $realtimeData = is_array($response->selectCmDeviceReturn->SelectCmDeviceResult->CmNodes->item) ?
                            $response->selectCmDeviceReturn->SelectCmDeviceResult->CmNodes->item :
                            [$response->selectCmDeviceReturn->SelectCmDeviceResult->CmNodes->item];
            return $realtimeData;

        } catch (SoapFault $e) {
            Log::error("RisPortSoap@queryRisPort ({$this->ucm->name}): Received Error Response", [
                [ 'faultstring' => $e->faultstring ],
                [ 'message' => $e->getMessage() ],
                [ 'last request' => $this->__getLastRequest() ],
                [ 'last response' => $this->__getLastResponse() ],
                [ 'last request headers' => $this->__getLastRequestHeaders() ]
            ]);

            // The typo in the error message below is intended.  It's what gets sent in the response from UCM :-)
            if (preg_match('/^AxisFault: Exceeded allowed rate for Reatime information/',$e->faultstring))
            {
                Log::error("RisPortSoap@queryRisPort ({$this->ucm->name}): Error was a throttle response.  " .
                                   "Sleeping 30 seconds"
                );
                sleep(30);
                $this->queryRisPort($phones);
            }
            Log::error("RisPortSoap@queryRisPort ({$this->ucm->name}): Error was not a throttle response.  Exiting");

            if($this->isFullSync) {
                Log::error("RisPortSoap@queryRisPort ({$this->ucm->name}): This was a full sync.  " .
                                    "Updating DB and checking Teams notification settings");

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
                        "RisPortSoap@queryRisPort ({$this->ucm->name}): Webex Teams notifications enabled.  
                             Sending error message now."
                    );

                    $message = "{$this->ucm->name} just finished syncing with **errors**:\n\n" .
                        "> Error Code: {$e->getCode()}\n\n" .
                        "> Error Message: {$e->getMessage()}";

                    $this->ucm->sendWebexTeamsNotification($message);
                }
            }

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
        Log::info("RisPortSoap@storeRealtimeData ({$this->ucm->name}): Storing Realtime Data locally");
        foreach($realtimeData as $cmNode) {

            if(!$cmNode->CmDevices) {
                Log::info("RisPortSoap@storeRealtimeData ({$this->ucm->name}): $cmNode->Name has no registered devices");
                continue;
            }

            Log::info("RisPortSoap@storeRealtimeData ({$this->ucm->name}): $cmNode->Name does have registered devices.  Setting device iterator");
            $devices = is_array($cmNode->CmDevices->item) ? $cmNode->CmDevices->item : [$cmNode->CmDevices->item];

            Log::info("RisPortSoap@storeRealtimeData ({$this->ucm->name}): Iterating devices");
            foreach($devices as $data) {
                Log::debug("RisPortSoap@storeRealtimeData ({$this->ucm->name}): Processing item", [ $data ]);

                if(!isset($data->Name)) continue;

                $phone = Phone::where([
                    'name' => $data->Name,
                    'ucm_id' => $this->ucm->id
                ])->first();
                Log::debug("RisPortSoap@storeRealtimeData ({$this->ucm->name}): Updating IP Phone ", [
                    'phoneId' => $phone->id
                ]);

                if(isset($data->LinesStatus->item)) {
                    $lines = array_map(function($line) {
                        return $line->DirectoryNumber;
                    }, is_array($data->LinesStatus->item) ? $data->LinesStatus->item : [$data->LinesStatus->item]);
                }

                $currentStatus = [
                    'UcmNode' => $cmNode->Name,
                    'Status' => $data->Status,
                    'StatusReason' => $data->StatusReason,
                    'Protocol' => $data->Protocol,
                    'NumOfLines' => $data->NumOfLines,
                    'Lines' => $lines ?? '',
                    'ActiveLoadID' => $data->ActiveLoadID,
                    'InactiveLoadID' => $data->InactiveLoadID,
                    'DownloadStatus' => $data->DownloadStatus,
                    'DownloadFailureReason' => $data->DownloadFailureReason,
                    'IPAddress' => $data->IPAddress->item->IP ?? '',
                    'UCMTimestamp' => $data->TimeStamp,
                    'CIP3Timestamp' => Carbon::now()->timestamp
                ];

                Log::debug("RisPortSoap@storeRealtimeData ({$this->ucm->name}): Setting current status ", [
                    'currentStatus' => $currentStatus
                ]);

                if($phone->realtime_data) {
                    $statuses = $phone->realtime_data;
                    Log::debug("RisPortSoap@storeRealtimeData ({$this->ucm->name}): Adding new status to DB ", [
                        'statuses_before' => $statuses,

                    ]);
                    array_unshift($statuses, $currentStatus);
                    $phone->realtime_data = $statuses;
                    Log::debug("RisPortSoap@storeRealtimeData ({$this->ucm->name}): Adding new status to DB ", [
                        'statuses_after' => $statuses,

                    ]);
                } else {
                    Log::debug("RisPortSoap@storeRealtimeData ({$this->ucm->name}): DB status is empty.  " .
                        "Adding current status"
                    );
                    $phone->realtime_data = [$currentStatus];
                }

                Log::debug("RisPortSoap@storeRealtimeData ({$this->ucm->name}): Storing phone data");
                $phone->status = $currentStatus['Status'];
                $phone->save();
            }
        }
        Log::info("RisPortSoap@storeRealtimeData ({$this->ucm->name}): Storing Realtime Data complete");
    }

    /**
     * @param $phones
     */
    private function buildRisPortPhoneArray($phones): void
    {
        Log::info("RisPortSoap@buildRisPortPhoneArray ({$this->ucm->name}): Building RisPort Item array");
        foreach ((array)$phones as $phone) {
            array_push($this->phones, ['Item' => $phone]);
        }
        Log::info("RisPortSoap@buildRisPortPhoneArray ({$this->ucm->name}): Set RisPhoneArray - ", [
            count($this->phones)
        ]);
    }
}
