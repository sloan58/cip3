<?php

namespace App\Console\Commands;

use App\Models\Ucm;
use App\Models\Phone;
use App\Models\BgImage;
use Illuminate\Console\Command;
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
        $fileName = $this->ask('What is the csv filename to provision?');
        $fileNameAndPath = "bps/$fileName";

        if (!$fileName || !Storage::disk('public')->exists($fileNameAndPath)) {
            $this->info("Sorry, I can't find a file named $fileName");
            exit;
        }

        $file = fopen(Storage::disk('public')->path($fileNameAndPath), "r");

        while (($data = fgetcsv($file)) !== false) {
            $phone = Ucm::where('ip_address', $data[2])
                            ->first()
                            ->phones()
                            ->where('name', $data[0])
                            ->first();
            if (!$phone) {
                \Log::error('Cloud not locate phone', [
                    'csvPhone' => $data[0],
                    'csvUcm' => $data[2],
                    'dbPhone' => $phone,
                ]);
                continue;
            }
            $image = $phone->bgImages()->where('name', $data[1])->first();

            if (!$image) {
                \Log::error('Cloud not locate image', [
                    'csvPhone' => $data[0],
                    'csvImage' => $data[1],
                    'csvUcm' => $data[2],
                    'dbPhone' => $phone,
                    'dbImage' => $image->image ?? 'Not Found'
                ]);
                continue;
            }

            PushPhoneBackgroundImageJob::dispatch(
                $phone,
                'bpsAdmin@cip3.com',
                $image->image
            );
        }
    }
}
