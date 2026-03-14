<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Exception;

class FirewallController extends Controller
{
    /**
     * Show firewall rules for a router
     * Tab is filter, nat, or mangle
     */
    public function show(Router $router, string $tab = 'filter')
    {
        return view('firewall.show', compact('router', 'tab'));
    }

    /**
     * Fetch firewall rules via AJAX
     */
    public function data(Router $router, string $tab = 'filter')
    {
        try {
            $service = (new MikrotikService())->connect($router);

            $rules = match($tab) {
                'nat'    => $service->getFirewallNatRest(),
                'mangle' => $service->getFirewallMangleRest(),
                default  => $service->getFirewallFilterRest(),
            };

            return response()->json([
                'success' => true,
                'rules'   => $rules,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle a rule on/off
     */
    public function toggle(Request $request, Router $router)
    {
        $request->validate([
            'id'      => 'required|string',
            'chain'   => 'required|in:filter,nat,mangle',
            'disable' => 'required|boolean',
        ]);

        try {
            $service = (new MikrotikService())->connect($router);

            if ($request->disable) {
                $service->disableFirewallRule($request->chain, $request->id);
            } else {
                $service->enableFirewallRule($request->chain, $request->id);
            }

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add a new firewall rule
     */
    public function store(Request $request, Router $router)
    {
        $request->validate([
            'chain'   => 'required|string',
            'action'  => 'required|string',
            'comment' => 'nullable|string',
            'src_address' => 'nullable|string',
            'dst_address' => 'nullable|string',
            'protocol'    => 'nullable|string',
            'dst_port'    => 'nullable|string',
        ]);

        try {
            $service = (new MikrotikService())->connect($router);

            $params = array_filter([
                'chain'       => $request->chain,
                'action'      => $request->action,
                'comment'     => $request->comment,
                'src-address' => $request->src_address,
                'dst-address' => $request->dst_address,
                'protocol'    => $request->protocol,
                'dst-port'    => $request->dst_port,
            ]);

            $service->addFirewallRule($params);

            return back()->with('success', 'Firewall rule added successfully.');

        } catch (Exception $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
}