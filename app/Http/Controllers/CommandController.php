<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\CommandLog;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Exception;

class CommandController extends Controller
{
    // Show command terminal for a single router
    public function index(Router $router)
    {
        $logs = $router->commandLogs()
            ->latest()
            ->take(20)
            ->get();

        return view('commands.index', compact('router', 'logs'));
    }

    // Execute a command on a single router
    public function execute(Request $request, Router $router)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        $command = trim($request->command);
        $start = microtime(true);

        try {
            $service = (new MikrotikService())->connect($router);
            $response = $service->command($command);
            $executionTime = round((microtime(true) - $start) * 1000);

            $log = CommandLog::create([
                'router_id'      => $router->id,
                'command'        => $command,
                'response'       => $response,
                'status'         => 'success',
                'execution_time' => $executionTime,
            ]);

            return back()->with('success', "Command executed in {$executionTime}ms");

        } catch (Exception $e) {
            CommandLog::create([
                'router_id' => $router->id,
                'command'   => $command,
                'status'    => 'failed',
                'error'     => $e->getMessage(),
            ]);

            return back()->with('error', "Failed: " . $e->getMessage());
        }
    }

    // Execute a command on multiple routers at once
    public function bulkExecute(Request $request)
    {
        $request->validate([
            'command'    => 'required|string',
            'router_ids' => 'required|array',
            'router_ids.*' => 'exists:routers,id',
        ]);

        $command = trim($request->command);
        $routers = Router::whereIn('id', $request->router_ids)->get();
        $results = [];

        foreach ($routers as $router) {
            $start = microtime(true);
            try {
                $service = (new MikrotikService())->connect($router);
                $response = $service->command($command);
                $executionTime = round((microtime(true) - $start) * 1000);

                CommandLog::create([
                    'router_id'      => $router->id,
                    'command'        => $command,
                    'response'       => $response,
                    'status'         => 'success',
                    'execution_time' => $executionTime,
                ]);

                $results[] = [
                    'router'   => $router->name,
                    'status'   => 'success',
                    'response' => $response,
                    'time'     => $executionTime,
                ];

            } catch (Exception $e) {
                CommandLog::create([
                    'router_id' => $router->id,
                    'command'   => $command,
                    'status'    => 'failed',
                    'error'     => $e->getMessage(),
                ]);

                $results[] = [
                    'router' => $router->name,
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ];
            }
        }

        return view('commands.bulk_results', compact('results', 'command'));
    }
}