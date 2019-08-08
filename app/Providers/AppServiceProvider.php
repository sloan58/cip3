<?php

namespace App\Providers;

use App\Models\BgImage;
use App\Observers\BgImageObserver;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Redis::enableEvents();
        BgImage::observe(BgImageObserver::class);
    }
}
