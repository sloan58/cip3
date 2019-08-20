<?php

namespace App\Observers;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;

class ReportObserver
{
    /**
     * Handle the report "created" event.
     *
     * @param Report $report
     * @return void
     */
    public function created(Report $report)
    {
        //
    }

    /**
     * Handle the report "updated" event.
     *
     * @param Report $report
     * @return void
     */
    public function updated(Report $report)
    {
        //
    }

    /**
     * Handle the report "deleted" event.
     *
     * @param Report $report
     * @return void
     */
    public function deleted(Report $report)
    {
        \Log::info('ReportObserver@deleted: Fired');
        \Log::info('ReportObserver@deleted: Deleting report from disk');

            Storage::delete(
                sprintf("public/%s",
                    $report->filename
                )
            );
        \Log::info('ReportObserver@deleted: Report deleted');
    }

    /**
     * Handle the report "restored" event.
     *
     * @param Report $report
     * @return void
     */
    public function restored(Report $report)
    {
        //
    }

    /**
     * Handle the report "force deleted" event.
     *
     * @param Report $report
     * @return void
     */
    public function forceDeleted(Report $report)
    {
        //
    }
}
