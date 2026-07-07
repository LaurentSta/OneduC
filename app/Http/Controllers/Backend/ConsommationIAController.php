<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\ConsommationIADashboardService;

class ConsommationIAController extends Controller
{
    public function index(ConsommationIADashboardService $dashboardService)
    {
        return view('admin.backend.pilotage.consommation_ia', [
            'formateurs' => $dashboardService->resumeParFormateur(),
        ]);
    }
}
