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

        $phoneModels = $this->buildPhoneModelsChart();

        $demoChart2 = app()->chartjs
            ->name('topContributorsChart2')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['One', 'Two', 'Three'])
            ->datasets([
                [
                    'backgroundColor' => RandomColor::many(count(['One', 'Two', 'Three'])),
                    'hoverBackgroundColor' => ['#FF6384', '#36A2EB'],
                    'data' => [1, 2, 3]
                ]
            ])
            ->options([]);

        $ucms = Ucm::all()->map(function($ucm) {
            return [ $ucm->name => $ucm->phones->count() ];
        });

        $labels = $ucms->map(function($ucm) {
            return key($ucm);
        });

        $data = $ucms->map(function($ucm) {
            return [
                'backgroundColor' => RandomColor::one(),
                'hoverBackgroundColor' => ['#FF6384', '#36A2EB'],
                'data' => end($ucm)
            ];
        });

        dump($labels, $data);

        $demoChart3 = app()->chartjs
            ->name('topContributorsChart3')
            ->type('bar')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['Cluster1', 'Cluster1'])
            ->datasets([
                [
                    "label" => "My First dataset",
                    'backgroundColor' => ['rgba(255, 99, 132, 0.2)'],
                    'data' => [69]
                ],
                [
                    "label" => "My Second dataset",
                    'backgroundColor' => ['rgba(54, 162, 235, 0.2)'],
                    'data' => [12]
                ]
            ])
            ->options([
                'legend' => [
                    'display' => true
                ]
            ]);

        return view('dashboard', compact('phoneModels', 'demoChart2', 'demoChart3'));
    }

    /**
     * Create the Dashboard phone models chart
     *
     * @return mixed
     */
    private function buildPhoneModelsChart()
    {
        Log::info("DashboardController@buildPhoneModelsChart: Fetching data for Phone model counts");
        $modelsAndCounts = \DB::table('phones')
            ->select(\DB::raw('model, count(*) count'))
            ->where('model', 'LIKE', 'Cisco %')
            ->groupBy('model')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        Log::info("DashboardController@buildPhoneModelsChart: Building labels and counts");
        $labels = array_map(function ($stat) {
            return $stat->model;
        }, $modelsAndCounts->toArray());

        $counts = array_map(function ($stat) {
            return $stat->count;
        }, $modelsAndCounts->toArray());

        Log::info("DashboardController@buildPhoneModelsChart: Creating ChartJS phoneModels Chart");
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
            ->options([]);
        return $phoneModels;
    }
}
