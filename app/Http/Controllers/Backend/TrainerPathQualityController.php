<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\TrainerPathQualityDashboardService;

class TrainerPathQualityController extends Controller
{
    public function index(TrainerPathQualityDashboardService $dashboardService)
    {
        return view('admin.backend.pilotage.qualite_parcours_formateur', [
            'dashboard' => $dashboardService->moduleTwoDashboard(),
        ]);
    }
}
