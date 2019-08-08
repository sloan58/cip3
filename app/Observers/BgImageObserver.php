<?php

namespace App\Observers;

use App\Models\BgImage;
use Illuminate\Support\Facades\Storage;

class BgImageObserver
{
    /**
     * Handle the bg image "created" event.
     *
     * @param BgImage $bgImage
     * @return void
     */
    public function created(BgImage $bgImage)
    {
        //
    }

    /**
     * Handle the bg image "updated" event.
     *
     * @param BgImage $bgImage
     * @return void
     */
    public function updated(BgImage $bgImage)
    {
        //
    }

    /**
     * Handle the bg image "deleted" event.
     *
     * @param BgImage $bgImage
     * @return void
     */
    public function deleted(BgImage $bgImage)
    {
        \Log::info('observer fired');
        $thumbnailImageName = sprintf(
            "%s_thumb.png",
            basename($bgImage->image, '.png')
        );

        foreach([$bgImage->image, $thumbnailImageName] as $file) {
            Storage::delete(
                sprintf("public/backgrounds/%s/%s",
                    $bgImage->dimensions,
                    $file
                )
            );
        }
    }

    /**
     * Handle the bg image "restored" event.
     *
     * @param BgImage $bgImage
     * @return void
     */
    public function restored(BgImage $bgImage)
    {
        //
    }

    /**
     * Handle the bg image "force deleted" event.
     *
     * @param BgImage $bgImage
     * @return void
     */
    public function forceDeleted(BgImage $bgImage)
    {
        //
    }
}
