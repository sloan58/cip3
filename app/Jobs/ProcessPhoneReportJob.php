<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Ucm;
use Laracsv\Export;
use App\Models\Phone;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use League\Csv\CannotInsertRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessPhoneReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Report
     */
    private $report;
    private $ucms;

    /**
     * Create a new job instance.
     *
     * @param Report $report
     * @param $ucms
     */
    public function __construct(Report $report, $ucms)
    {
        $this->ucms = $ucms;
        $this->report = $report;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws CannotInsertRecord
     */
    public function handle()
    {
        Log::info(
            'ProcessPhoneReportJob@handle: Starting report',
            [$this->report->name, $this->ucms]
        );

        $csvExporter = new Export();
        $phones = $this->ucms == "All" ? Phone::get() : Ucm::where('name', $this->ucms)->first()->phones;

        Log::info(
            'ProcessPhoneReportJob@handle: Retrieved Phones for processing',
            [$phones->count()]
        );

        // Register the hook before building
        $csvExporter->beforeEach(function ($phone) {
            $phone->Status = $phone->realtime_data[0]['Status'];
            $phone->StatusReason = $phone->realtime_data[0]['StatusReason'];
            $phone->cip3_ucm = $phone->ucm->name;
            $phone->UcmNode = $phone->realtime_data[0]['UcmNode'];
            $phone->Protocol = $phone->realtime_data[0]['Protocol'];
            $phone->IPAddress = $phone->realtime_data[0]['IPAddress'];
            $phone->NumOfLines = $phone->realtime_data[0]['NumOfLines'];
            $phone->ActiveLoadID = $phone->realtime_data[0]['ActiveLoadID'];
            $phone->InactiveLoadID = $phone->realtime_data[0]['InactiveLoadID'];
            if($phone->realtime_data[0]['UCMTimestamp']) {
                $phone->UCMTimestamp = Carbon::createFromTimestamp($phone->realtime_data[0]['UCMTimestamp'])
                    ->toDateTimeString();
            }
            if($phone->realtime_data[0]['CIP3Timestamp']) {
                $phone->CIP3Timestamp = Carbon::createFromTimestamp($phone->realtime_data[0]['CIP3Timestamp'])
                    ->toDateTimeString();
            }
        });

        Log::info('ProcessPhoneReportJob@handle: Building report output');

        $csvExporter->build($phones, [
            'name',
            'description',
            'model',
            'device_pool',
            'cip3_ucm',
            'Status',
            'StatusReason',
            'UcmNode',
            'IPAddress',
            'Protocol',
            'NumOfLines',
            'ActiveLoadID',
            'InactiveLoadID',
            'UCMTimestamp',
            'CIP3Timestamp',
        ]);

        Log::info('ProcessPhoneReportJob@handle: Creating CSV writer and storing file');
        $csvWriter = $csvExporter->getWriter();
        Storage::put($this->report->filename, $csvWriter->getContent());

        Log::info('ProcessPhoneReportJob@handle: Job complete.  Updating Report object to "finished"');
        $this->report->status = "finished";
        $this->report->save();
    }
}
