<?php

namespace App\Http\Controllers;

use App\Models\Router;
use Illuminate\Http\Request;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Log;

class RouterController extends Controller
{
    // Show all routers
    public function index()
    {
        $routers = Router::orderBy('name')->get();
        return view('routers.index', compact('routers'));
    }

    // Show create form
    public function create()
    {
        return view('routers.create');
    }

    // Save new router
    public function store(Request $request, Router $router)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'ip_address'  => 'required|ip',
            'api_port'    => 'required|integer|min:1|max:65535',
            'username'    => 'required|string|max:255',
            'password'    => 'required|string|max:255',
            'group'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        Log::info('Router created', [
            'router_id'   => $router->id,
            'router_name' => $router->name,
            'router_ip'   => $router->ip_address,
        ]);
        
        Router::create($validated);

        return redirect()->route('routers.index')
            ->with('success', 'Router added successfully.');
    }

    // Show single router
    public function show(Router $router)
    {
        return view('routers.show', compact('router'));
    }

    // Show edit form
    public function edit(Router $router)
    {
        return view('routers.edit', compact('router'));
    }

    // Update router
    public function update(Request $request, Router $router)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'ip_address'  => 'required|ip',
            'api_port'    => 'required|integer|min:1|max:65535',
            'username'    => 'required|string|max:255',
            'password'    => 'nullable|string|max:255',
            'group'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Only update password if a new one was provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $router->update($validated);

        return redirect()->route('routers.index')
            ->with('success', 'Router updated successfully.');
    }

    // Delete router
    public function destroy(Router $router)
    {
        Log::warning('Router deleted', [
            'router_id'   => $router->id,
            'router_name' => $router->name,
        ]);
        $router->delete();

        return redirect()->route('routers.index')
            ->with('success', 'Router deleted successfully.');
    }

    public function test(Router $router)
    {
        $isOnline = MikrotikService::testConnection($router);

        return back()->with(
            $isOnline ? 'success' : 'error',
            $isOnline
                ? "✅ Connected to {$router->name} successfully!"
                : "❌ Could not connect to {$router->name}. Check IP, port and credentials."
        );
    }
    
}