<?php

namespace App\Console\Commands\BackgroundImages;

use App\Models\Ucm;
use App\ApiClients\AxlSoap;
use App\ApiClients\RisPortSoap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VerifyPhoneReadiness extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cip3:verify-phone-readiness';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify a list of phones is ready for a bg image push';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info(__METHOD__ . ": Starting image verification process");

        Log::info(__METHOD__ . ": Collecting filename");
        $fileName = $this->ask('What is the csv filename to verify?');
        $fileNameAndPath = "bps/$fileName";

        Log::info(__METHOD__ . ": Received $fileName and set $fileNameAndPath");
        if (!$fileName || !Storage::disk('public')->exists($fileNameAndPath)) {
            Log::info(__METHOD__ . ": Received $fileNameAndPath does not exist.  Exiting");
            $this->info("Sorry, I can't find a file named $fileName");
            exit;
        }

        Log::info(__METHOD__ . ": Collecting UCM IP address");
        $ucmIp = $this->ask('What is the IP Address of the UCM Server?');
        $ucm = Ucm::where('ip_address', $ucmIp)->first();

        Log::info(__METHOD__ . ": Received $ucmIp");
        if (!$ucm) {
            Log::info(__METHOD__ . ": Could not locate a DB record with ip address of $ucmIp.  Exiting");
            $this->info("Sorry, I can't find the UCM with ip address $ucmIp");
            exit;
        }

        Log::info(__METHOD__ . ": Opening csv file");
        $file = fopen(Storage::disk('public')->path($fileNameAndPath), "r");

        Log::info(__METHOD__ . ": Creating phone association array");
        $phones = [];
        while (($data = fgetcsv($file)) !== false) {
            $phones[] = $data[0];
        }
        Log::info(__METHOD__ . ": Closing file handle");
        fclose($file);

        Log::info(__METHOD__ . ": Creating AxlSoap client");
        $axl = new AxlSoap($ucm);

        Log::info(__METHOD__ . ": Iterating phones");
        foreach ($phones as $phoneName) {
            Log::info(__METHOD__ . ": Working phone $phoneName");
            if ($phone = $ucm->phones()->where('name', $phoneName)->first()) {
                Log::info(__METHOD__ . ": $phoneName found in DB");
                $ris = new RisPortSoap($phone->ucm, false);
                $ris->collectRealtimeData([$phone->name]);
                Log::info(__METHOD__ . ": Collected realtime data for $phoneName");

                $results = $axl->supportsBackgroundApi($phone);
                Log::info(__METHOD__ . ": Received supportsBackground API response for $phoneName");
                if ($results['success']) {
                    Log::info(__METHOD__ . ": $phoneName supportsBackground was successful");
                    $message = sprintf('%s is ready', $phone->name);
                    Log::info("VerifyResults ($fileName): $message");
                } else {
                    Log::info(__METHOD__ . ": $phoneName supportsBackground was unsuccessful");
                    $message = sprintf('%s is not ready due to : %s', $phone->name, $results['reason']);
                    Log::info("VerifyResults ($fileName): $message");
                }
            } else {
                $message = sprintf('%s does not exist in CIP3', $phoneName);
                Log::info("VerifyResults ($fileName): $message");
            }
        }
    }
}
