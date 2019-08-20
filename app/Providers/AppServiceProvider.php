<?php

namespace App\Providers;

use App\Models\Report;
use App\Models\BgImage;
use App\Models\BgImageHistory;
use App\Observers\ReportObserver;
use App\Observers\BgImageObserver;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use App\Observers\BgImageHistoryObserver;

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
        Report::observe(ReportObserver::class);
        BgImage::observe(BgImageObserver::class);
        BgImageHistory::observe(BgImageHistoryObserver::class);
    }
}
