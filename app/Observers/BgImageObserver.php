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
     * Handle the bg image "updating" event.
     *
     * @param BgImage $bgImage
     * @return void
     */
    public function updating(BgImage $bgImage)
    {
        \Log::info('BgImageObserver@updating: Fired');

        if($bgImage->getOriginal('image') && $bgImage->getOriginal('image') != $bgImage->image) {
            \Log::info('BgImageObserver@updating: Image is being updated.  Deleting old images');
            $this->deleteImages($bgImage->getOriginal('image'), $bgImage->dimensions);
        }

        if($bgImage->getOriginal('dimensions') && $bgImage->getOriginal('dimensions') != $bgImage->dimensions) {
            \Log::info('BgImageObserver@updating: Image dimensions have changed.  Moving image location.');
            $this->moveImageLocation($bgImage->image, $bgImage->dimensions, $bgImage->getOriginal('dimensions'));
        }
    }

    /**
     * Handle the bg image "deleted" event.
     *
     * @param BgImage $bgImage
     * @return void
     */
    public function deleted(BgImage $bgImage)
    {
        \Log::info('BgImageObserver@deleted: Fired');
        $this->deleteImages($bgImage->image, $bgImage->dimensions);
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

    /**
     * Delete BgImages from disk
     *
     * @param $image
     * @param $dimensions
     */
    private function deleteImages($image, $dimensions)
    {
        $thumbnailImageName = $this->getThumbnailFileName($image);

        \Log::info('BgImageObserver@deleteImages: Deleting images: ', [
            $image, $thumbnailImageName
        ]);

        foreach([$image, $thumbnailImageName] as $file) {
            Storage::delete(
                sprintf("public/backgrounds/%s/%s",
                    $dimensions,
                    $file
                )
            );
        }
        \Log::info('BgImageObserver@deleteImages: Images deleted');
    }

    /**
     * Relocate BgImage files
     *
     * @param $image
     * @param $newDimensions
     * @param $oldDimensions
     */
    private function moveImageLocation($image, $newDimensions, $oldDimensions)
    {
        $thumbnailImageName = $this->getThumbnailFileName($image);

        \Log::info('BgImageObserver@moveImageLocation: Moving images: ', [
            $image, $thumbnailImageName
        ]);

        foreach([$image, $thumbnailImageName] as $file) {
            Storage::move(
                sprintf("public/backgrounds/%s/%s",
                    $oldDimensions,
                    $file
                ),
                sprintf("public/backgrounds/%s/%s",
                    $newDimensions,
                    $file
                )
            );
        }
        \Log::info('BgImageObserver@moveImageLocation: Images moved');
    }

    /**
     * Compute the thumbnail image name
     *
     * @param $image
     * @return string
     */
    private function getThumbnailFileName($image)
    {
        return sprintf(
            "%s_thumb.png",
            basename($image, '.png')
        );
    }
}
