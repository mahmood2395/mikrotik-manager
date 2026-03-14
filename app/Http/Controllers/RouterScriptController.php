<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\Script;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Exception;

class RouterScriptController extends Controller
{
    /**
     * Show all scripts currently living on a specific router
     */
    public function index(Router $router)
    {
        try {
            $service = (new MikrotikService())->connect($router);
            $routerScripts = $service->getRouterScripts();
        } catch (Exception $e) {
            return back()->with('error', "Could not connect to {$router->name}: " . $e->getMessage());
        }

        // Check which router scripts have a matching name in the app DB
        $appScriptNames = Script::pluck('name')->toArray();

        return view('router-scripts.index', compact('router', 'routerScripts', 'appScriptNames'));
    }

    /**
     * Import a single script from router into the app DB
     */
    public function import(Request $request, Router $router)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        try {
            $service = (new MikrotikService())->connect($router);
            $routerScript = $service->getRouterScript($request->name);

            if (!$routerScript) {
                return back()->with('error', "Script '{$request->name}' not found on router.");
            }

            $existing = Script::where('name', $request->name)->first();

            if ($existing) {
                $existing->update([
                    'content'     => $routerScript['source'] ?? '',
                    'description' => $routerScript['comment'] ?: $existing->description,
                ]);
                return back()->with('success', "Script '{$request->name}' updated from router.");
            } else {
                Script::create([
                    'name'        => $routerScript['name'],
                    'content'     => $routerScript['source'] ?? '',
                    'description' => $routerScript['comment'] ?? null,
                    'category'    => 'imported',
                ]);
                return back()->with('success', "Script '{$request->name}' imported successfully.");
            }

        } catch (\Exception $e) {
            return back()->with('error', "Import failed: " . $e->getMessage());
        }
    }

    /**
     * Import ALL scripts from a router into app DB
     */
    public function importAll(Router $router)
    {
        try {
            $service = (new MikrotikService())->connect($router);
            $routerScripts = $service->importScripts();
        } catch (Exception $e) {
            return back()->with('error', "Could not connect: " . $e->getMessage());
        }

        $imported = 0;
        $updated  = 0;

        foreach ($routerScripts as $rs) {
            $existing = Script::where('name', $rs['name'])->first();

            if ($existing) {
                $existing->update([
                    'content'     => $rs['source'],
                    'description' => $rs['comment'] ?: $existing->description,
                ]);
                $updated++;
            } else {
                Script::create([
                    'name'        => $rs['name'],
                    'content'     => $rs['source'],
                    'description' => $rs['comment'] ?: null,
                    'category'    => 'imported',
                ]);
                $imported++;
            }
        }

        return back()->with('success', "Done — {$imported} imported, {$updated} updated.");
    }

    /**
     * Push an app script to this router
     * Updates if exists on router, creates if not
     */
    public function push(Request $request, Router $router)
    {
        $request->validate([
            'script_id' => 'required|exists:scripts,id',
        ]);

        $script = Script::findOrFail($request->script_id);

        try {
            $service = (new MikrotikService())->connect($router);

            // Check if script exists on router
            $existing = $service->getRouterScript($script->name);
            $action   = $existing ? 'updated' : 'created';

            $service->pushScript($script->name, $script->content);

            return back()->with('success', "Script '{$script->name}' {$action} on {$router->name}.");

        } catch (Exception $e) {
            return back()->with('error', "Push failed: " . $e->getMessage());
        }
    }

    /**
     * Compare same-name scripts across all routers
     */
    public function compare(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $scriptName = $request->name;
        $routers    = Router::active()->get();
        $results    = [];

        foreach ($routers as $router) {
            try {
                $service      = (new MikrotikService())->connect($router);
                $routerScript = $service->getRouterScript($scriptName);

                $results[] = [
                    'router'  => $router,
                    'found'   => (bool) $routerScript,
                    'content' => $routerScript['source'] ?? null,
                ];
            } catch (Exception $e) {
                $results[] = [
                    'router'  => $router,
                    'found'   => false,
                    'content' => null,
                    'error'   => $e->getMessage(),
                ];
            }
        }

        // Also get app DB version if exists
        $appScript = Script::where('name', $scriptName)->first();

        return view('router-scripts.compare', compact('scriptName', 'results', 'appScript'));
    }
}