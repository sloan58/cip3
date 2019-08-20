<?php

namespace App\Observers;

use App\Models\BgImageHistory;
use Illuminate\Support\Facades\Storage;

class BgImageHistoryObserver
{
    /**
     * Handle the bg image history "created" event.
     *
     * @param BgImageHistory $bgImageHistory
     * @return void
     */
    public function created(BgImageHistory $bgImageHistory)
    {
        //
    }

    /**
     * Handle the bg image history "updated" event.
     *
     * @param BgImageHistory $bgImageHistory
     * @return void
     */
    public function updated(BgImageHistory $bgImageHistory)
    {
        //
    }

    /**
     * Handle the bg image history "deleted" event.
     *
     * @param BgImageHistory $bgImageHistory
     * @return void
     */
    public function deleted(BgImageHistory $bgImageHistory)
    {
        \Log::info('BgImageHistoryObserver@deleted: Fired');
        Storage::delete(
            sprintf("public/screenshots/%s_%s.png",
                $bgImageHistory->id,
                $bgImageHistory->phone
            )
        );

        \Log::info('BgImageHistoryObserver@deleted: Image deleted');
    }

    /**
     * Handle the bg image history "restored" event.
     *
     * @param BgImageHistory $bgImageHistory
     * @return void
     */
    public function restored(BgImageHistory $bgImageHistory)
    {
        //
    }

    /**
     * Handle the bg image history "force deleted" event.
     *
     * @param BgImageHistory $bgImageHistory
     * @return void
     */
    public function forceDeleted(BgImageHistory $bgImageHistory)
    {
        //
    }
}
