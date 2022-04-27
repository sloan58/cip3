<?php

namespace App\Console\Commands\BackgroundImages;

use App\Models\Ucm;
use App\Models\Phone;
use App\Models\BgImage;
use App\ApiClients\AxlSoap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\PushPhoneBackgroundImageJob;

class BulkImageProvisioningCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:bps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk Provisioning Service to push background images';

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
        Log::info('BulkImageProvisioningCommand@handle: Starting image bps process');

        Log::info('BulkImageProvisioningCommand@handle: Collecting filename');
        $fileName = $this->ask('What is the csv filename to provision?');
        $fileNameAndPath = "bps/$fileName";

        Log::info("BulkImageProvisioningCommand@handle: Received $fileName and set $fileNameAndPath");
        if (!$fileName || !Storage::disk('public')->exists($fileNameAndPath)) {
            Log::info("BulkImageProvisioningCommand@handle: Received $fileNameAndPath does not exist.  Exiting");
            $this->info("Sorry, I can't find a file named $fileName");
            exit;
        }

        Log::info("BulkImageProvisioningCommand@handle: Collecting UCM IP address");
        $ucmIp = $this->ask('What is the IP Address of the UCM Server?');
        $ucm = Ucm::where('ip_address', $ucmIp)->first();

        Log::info("BulkImageProvisioningCommand@handle: Received $ucmIp");
        if (!$ucm) {
            Log::info("BulkImageProvisioningCommand@handle: Could not locate a DB record with ip address of $ucmIp.  Exiting");
            $this->info("Sorry, I can't find the UCM with ip address $ucmIp");
            exit;
        }

        Log::info("BulkImageProvisioningCommand@handle: Opening csv file");
        $file = fopen(Storage::disk('public')->path($fileNameAndPath), "r");

        Log::info("BulkImageProvisioningCommand@handle: Creating phone association array");
        $phoneAssociationArray = [];
        while (($data = fgetcsv($file)) !== false) {
            array_push($phoneAssociationArray, $data[0]);
        }
        Log::info("BulkImageProvisioningCommand@handle: Closing file handle");
        fclose($file);

        Log::info("BulkImageProvisioningCommand@handle: Creating AxlSoap client");
        $axl = new AxlSoap($ucm);

        Log::info("BulkImageProvisioningCommand@handle: Handing off to make phone associations");
        $axl->bulkAssociatePhoneWithAppUser($phoneAssociationArray);

        Log::info("BulkImageProvisioningCommand@handle: Iterating csv to push image");
        $file = fopen(Storage::disk('public')->path($fileNameAndPath), "r");
        while (($data = fgetcsv($file)) !== false) {
            Log::info("BulkImageProvisioningCommand@handle: Querying for phone object $data[0]");
            $phone = $ucm->phones()->where('name', $data[0])->first();

            if (!$phone) {
                Log::info("BulkImageProvisioningCommand@handle: Could not find phone with name $data[0].  Continuing", [
                    'csvPhone' => $data[0],
                    'csvUcm' => $ucm->name,
                    'dbPhone' => $phone,
                ]);
                continue;
            }
            Log::info("BulkImageProvisioningCommand@handle: Querying for image object $data[1]");
            $image = $phone->bgImages()->where('name', $data[1])->first();

            if (!$image) {
                Log::info("BulkImageProvisioningCommand@handle: Could not find image with name $data[1]", [
                    'csvPhone' => $data[0],
                    'csvImage' => $data[1],
                    'csvUcm' => $ucm->name,
                    'dbPhone' => $phone,
                    'dbImage' => $image->image ?? 'Not Found'
                ]);
                continue;
            }

            Log::info("BulkImageProvisioningCommand@handle: Handing off to background image job");
            PushPhoneBackgroundImageJob::dispatch(
                $phone,
                'bpsAdmin@cip3.com',
                $image->image
            );
        }
    }
}
