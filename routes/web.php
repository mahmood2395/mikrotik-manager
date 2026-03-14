<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\ScriptController;
use App\Http\Controllers\RouterScriptController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\FirewallController;
use App\Models\Router;

// Public routes — login/register (handled by Breeze)
require __DIR__.'/auth.php';

// All app routes — protected by auth middleware
Route::middleware('auth')->group(function () {

    // Home — redirect to routers
    Route::get('/', fn() => redirect()->route('routers.index'));

    // Routers
    Route::resource('routers', RouterController::class);
    Route::post('routers/{router}/test', [RouterController::class, 'test'])
        ->name('routers.test');

    // Commands
    Route::get('routers/{router}/commands', [CommandController::class, 'index'])
        ->name('commands.index');
    Route::post('routers/{router}/commands', [CommandController::class, 'execute'])
        ->name('commands.execute');
    Route::get('commands/bulk', function() {
        return view('commands.bulk', ['routers' => Router::all()]);
    })->name('commands.bulk');
    Route::post('commands/bulk', [CommandController::class, 'bulkExecute'])
        ->name('commands.bulk.execute');

    // Scripts
    Route::resource('scripts', ScriptController::class);
    Route::post('scripts/{script}/execute', [ScriptController::class, 'execute'])
        ->name('scripts.execute');

    // Router Scripts
    Route::get('routers/{router}/scripts', [RouterScriptController::class, 'index'])
        ->name('router-scripts.index');
    Route::post('routers/{router}/scripts/import', [RouterScriptController::class, 'import'])
        ->name('router-scripts.import');
    Route::post('routers/{router}/scripts/import-all', [RouterScriptController::class, 'importAll'])
        ->name('router-scripts.import-all');
    Route::post('routers/{router}/scripts/push', [RouterScriptController::class, 'push'])
        ->name('router-scripts.push');
    Route::get('scripts/compare', [RouterScriptController::class, 'compare'])
        ->name('router-scripts.compare');

    // Monitoring
    Route::get('monitoring', [MonitoringController::class, 'overview'])
        ->name('monitoring.overview');
    Route::get('monitoring/all-status', [MonitoringController::class, 'allStatus'])
        ->name('monitoring.all-status');
    Route::get('routers/{router}/monitor', [MonitoringController::class, 'show'])
        ->name('monitoring.show');
    Route::get('routers/{router}/monitor/data', [MonitoringController::class, 'data'])
        ->name('monitoring.data');

    // Firewall
    Route::get('routers/{router}/firewall/{tab?}', [FirewallController::class, 'show'])
        ->name('firewall.show');
    Route::get('routers/{router}/firewall/{tab}/data', [FirewallController::class, 'data'])
        ->name('firewall.data');
    Route::post('routers/{router}/firewall/toggle', [FirewallController::class, 'toggle'])
        ->name('firewall.toggle');
    Route::post('routers/{router}/firewall/store', [FirewallController::class, 'store'])
        ->name('firewall.store');

});