<?php

namespace App\Http\Controllers;

use App\Models\Script;
use App\Models\Router;
use App\Models\ScriptExecution;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Exception;

class ScriptController extends Controller
{
    public function index()
    {
        $scripts = Script::withCount('executions')
            ->orderBy('name')
            ->get();

        return view('scripts.index', compact('scripts'));
    }

    public function create()
    {
        return view('scripts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content'     => 'required|string',
        ]);

        Script::create($validated);

        return redirect()->route('scripts.index')
            ->with('success', 'Script saved successfully.');
    }

    public function show(Script $script)
    {
        $executions = $script->executions()
            ->with('router')
            ->latest()
            ->take(30)
            ->get();

        $routers = Router::active()->orderBy('name')->get();

        return view('scripts.show', compact('script', 'executions', 'routers'));
    }

    public function edit(Script $script)
    {
        return view('scripts.edit', compact('script'));
    }

    public function update(Request $request, Script $script)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content'     => 'required|string',
        ]);

        $script->update($validated);

        return redirect()->route('scripts.index')
            ->with('success', 'Script updated successfully.');
    }

    public function destroy(Script $script)
    {
        $script->delete();

        return redirect()->route('scripts.index')
            ->with('success', 'Script deleted.');
    }

    // Execute script on selected routers
    public function execute(Request $request, Script $script)
    {
        $request->validate([
            'router_ids'   => 'required|array',
            'router_ids.*' => 'exists:routers,id',
        ]);

        $routers = Router::whereIn('id', $request->router_ids)->get();
        $results = [];

        foreach ($routers as $router) {
            $start = microtime(true);
            try {
                $service = (new MikrotikService())->connect($router);
                $output = $service->executeScript($script->name, $script->content);
                $executionTime = round((microtime(true) - $start) * 1000);

                ScriptExecution::create([
                    'script_id'      => $script->id,
                    'router_id'      => $router->id,
                    'status'         => 'success',
                    'output'         => json_encode($output),
                    'execution_time' => $executionTime,
                ]);

                $results[] = [
                    'router' => $router->name,
                    'status' => 'success',
                    'output' => $output,
                    'time'   => $executionTime,
                ];

            } catch (Exception $e) {
                ScriptExecution::create([
                    'script_id' => $script->id,
                    'router_id' => $router->id,
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

        return view('scripts.results', compact('script', 'results'));
    }
}