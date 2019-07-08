<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ucm;
use Colors\RandomColor;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;

class DashboardController extends Controller
{
    /**
     *  Generate CIP3 Dashboard
     *
     * @return Factory|View
     */
    public function index()
    {
        Log::info("DashboardController@index: Generating Dashboard charts");

        if(!Ucm::count()) {
            Log::info("DashboardController@index: There are no UCM servers.  Return noData = true");
            $noData = true;
            return view('dashboard', compact('noData'));
        }

        $phoneModels = $this->buildTop10PhoneModelsChart();

        $clusterCounts = $this->buildTotalPhoneCountChart();

        $regCounts = $this->buildRegisteredPhoneCountChart();

        $unRegCounts = $this->buildUnRegisteredPhoneCountChart();

        $unKnownCounts = $this->buildUnKnownPhoneCountChart();

        return view('dashboard', compact(
            'phoneModels',
            'clusterCounts',
            'regCounts',
            'unRegCounts',
            'unKnownCounts'
        ));
    }

    /**
     * Create the Dashboard phone models chart
     *
     * @return mixed
     */
    private function buildTop10PhoneModelsChart()
    {
        Log::info("DashboardController@buildTop10PhoneModelsChart: Fetching data for top 10 Phone model counts");
        $modelsAndCounts = \DB::table('phones')
            ->select(\DB::raw('model, count(*) count'))
            ->where('model', 'LIKE', 'Cisco %')
            ->groupBy('model')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        Log::info("DashboardController@buildTop10PhoneModelsChart: Building labels and counts");
        $labels = array_map(function ($stat) {
            return $stat->model;
        }, $modelsAndCounts->toArray());

        $counts = array_map(function ($stat) {
            return $stat->count;
        }, $modelsAndCounts->toArray());

        Log::info("DashboardController@buildTop10PhoneModelsChart: Creating ChartJS phoneModels Chart");
        $phoneModels = app()->chartjs
            ->name('phoneModels')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels($labels)
            ->datasets([
                [
                    'backgroundColor' => RandomColor::many(count($labels)),
                    'hoverBackgroundColor' => ['#FF6384', '#36A2EB'],
                    'data' => $counts
                ]
            ])
            ->options([
                'legend' => [
                    'display' => true,
                    'position' => 'left',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Phone Counts for all Clusters'
                ]
            ]);

        return $phoneModels;
    }

    /**
     * Create the Dashboard total phones chart
     *
     * @return mixed
     */
    private function buildTotalPhoneCountChart()
    {
        Log::info("DashboardController@buildTotalPhoneCountChart: Fetching data for total Phone counts");

        Log::info("DashboardController@buildTotalPhoneCountChart: Gathering all UCM records and counts");
        $data = Ucm::all()->pluck('totalPhoneCount', 'name')->toArray();

        Log::info("DashboardController@buildTotalPhoneCountChart: Creating ChartJS clusterCounts Chart");
        $clusterCounts = app()->chartjs
            ->name('clusterCounts')
            ->type('bar')
            ->size(['width' => 400, 'height' => 200])
            ->labels(array_keys($data))
            ->datasets([
                [
                    'backgroundColor' => RandomColor::many(count($data)),
                    'hoverBackgroundColor' => ['#FF6384', '#36A2EB'],
                    'data' => array_values($data)
                ]
            ])
            ->options([
                'legend' => [
                    'display' => false
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Totals'
                ]
            ]);

        return $clusterCounts;
    }

    /**
     * Create the Dashboard total phones chart
     *
     * @return mixed
     */
    private function buildRegisteredPhoneCountChart()
    {
        Log::info("DashboardController@buildTotalPhoneCountChart: Fetching data for registered Phone counts");

        Log::info("DashboardController@buildTotalPhoneCountChart: Gathering all UCM records and counts");
        $data = Ucm::all()->pluck('registeredPhoneCount', 'name')->toArray();

        Log::info("DashboardController@buildTotalPhoneCountChart: Creating ChartJS clusterCounts Chart");
        $regCounts = app()->chartjs
            ->name('regCounts')
            ->type('bar')
            ->size(['width' => 400, 'height' => 200])
            ->labels(array_keys($data))
            ->datasets([
                [
                    'backgroundColor' => RandomColor::many(count($data)),
                    'hoverBackgroundColor' => ['#FF6384', '#36A2EB'],
                    'data' => array_values($data)
                ]
            ])
            ->options([
                'legend' => [
                    'display' => false
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Registered Phones'
                ]
            ]);

        return $regCounts;
    }

    /**
     * Create the Dashboard total phones chart
     *
     * @return mixed
     */
    private function buildUnRegisteredPhoneCountChart()
    {
        Log::info("DashboardController@buildTotalPhoneCountChart: Fetching data for registered Phone counts");

        Log::info("DashboardController@buildTotalPhoneCountChart: Gathering all UCM records and counts");
        $data = Ucm::all()->pluck('unRegisteredPhoneCount', 'name')->toArray();

        Log::info("DashboardController@buildTotalPhoneCountChart: Creating ChartJS clusterCounts Chart");
        $regCounts = app()->chartjs
            ->name('unRegCounts')
            ->type('bar')
            ->size(['width' => 400, 'height' => 200])
            ->labels(array_keys($data))
            ->datasets([
                [
                    'backgroundColor' => RandomColor::many(count($data)),
                    'hoverBackgroundColor' => ['#FF6384', '#36A2EB'],
                    'data' => array_values($data)
                ]
            ])
            ->options([
                'legend' => [
                    'display' => false
                ],
                'title' => [
                    'display' => true,
                    'text' => 'UnRegistered Phones'
                ]
            ]);

        return $regCounts;
    }

    /**
     * Create the Dashboard total phones chart
     *
     * @return mixed
     */
    private function buildUnKnownPhoneCountChart()
    {
        Log::info("DashboardController@buildTotalPhoneCountChart: Fetching data for registered Phone counts");

        Log::info("DashboardController@buildTotalPhoneCountChart: Gathering all UCM records and counts");
        $data = Ucm::all()->pluck('unKnownPhoneCount', 'name')->toArray();

        Log::info("DashboardController@buildTotalPhoneCountChart: Creating ChartJS clusterCounts Chart");
        $regCounts = app()->chartjs
            ->name('unknownCounts')
            ->type('bar')
            ->size(['width' => 400, 'height' => 200])
            ->labels(array_keys($data))
            ->datasets([
                [
                    'backgroundColor' => RandomColor::many(count($data)),
                    'hoverBackgroundColor' => ['#FF6384', '#36A2EB'],
                    'data' => array_values($data)
                ]
            ])
            ->options([
                'legend' => [
                    'display' => false
                ],
                'title' => [
                    'display' => true,
                    'text' => 'UnKnown Phones'
                ]
            ]);

        return $regCounts;
    }
}
