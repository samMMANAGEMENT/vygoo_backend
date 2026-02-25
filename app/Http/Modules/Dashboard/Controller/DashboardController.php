<?php

namespace App\Http\Modules\Dashboard\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Dashboard\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getSummary()
    {
        try {
            $entityId = Auth::user()->entity_id;
            $summary = $this->dashboardService->getSummary($entityId);
            return response()->json($summary);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener resumen del dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
