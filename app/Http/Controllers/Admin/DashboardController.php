<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentSpa;
use App\Http\Controllers\Controller;
use App\Modules\Operations\Queries\DashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesCurrentSpa;

    public function index(Request $request, DashboardMetrics $metrics): View
    {
        $spa = $this->currentSpa($request);

        return view('admin.dashboard', [
            'spa' => $spa,
            'stats' => $metrics->counts($spa),
            'recentReservations' => $metrics->recentReservations($spa),
            'openHandovers' => $metrics->latestOpenHandovers($spa),
        ]);
    }
}
