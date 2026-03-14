<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Exception;

class MonitoringController extends Controller
{
    /**
     * Show monitoring dashboard for a single router
     */
    public function show(Router $router)
    {
        return view('monitoring.show', compact('router'));
    }

    /**
     * Fetch live data via AJAX — called by the dashboard
     * Returns JSON so the page can refresh without full reload
     */
    public function data(Router $router)
    {
        try {
            $service = (new MikrotikService())->connect($router);
            $data    = $service->getMonitoringData();

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Overview — all routers status at once
     */
    public function overview()
    {
        $routers = Router::active()->orderBy('name')->get();
        return view('monitoring.overview', compact('routers'));
    }

    /**
     * Fetch status for all routers — used by overview page
     */
    public function allStatus()
    {
        $routers = Router::active()->get();
        $results = [];

        foreach ($routers as $router) {
            try {
                $service   = (new MikrotikService())->connect($router);
                $resources = $service->getSystemResourcesRest();
                $identity  = $service->getIdentityRest();

                $results[$router->id] = [
                    'online'   => true,
                    'identity' => $identity,
                    'cpu'      => $resources['cpu-load'] ?? 0,
                    'memory'   => $this->memoryPercent($resources),
                    'uptime'   => $resources['uptime'] ?? 'unknown',
                    'version'  => $resources['version'] ?? 'unknown',
                ];

            } catch (Exception $e) {
                $results[$router->id] = [
                    'online' => false,
                    'error'  => $e->getMessage(),
                ];
            }
        }

        return response()->json($results);
    }

    private function memoryPercent(array $resources): int
    {
        $total = $resources['total-memory'] ?? 0;
        $free  = $resources['free-memory'] ?? 0;

        if ($total == 0) return 0;

        return round((($total - $free) / $total) * 100);
    }
}