<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Prometheus\Facades\Prometheus;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Prometheus::addGauge('http_requests_2xx')
            ->helpText('Successful HTTP requests')
            ->value(fn() => (int) cache()->get('metrics.requests.2xx', 0));

        Prometheus::addGauge('http_requests_4xx')
            ->helpText('Client error HTTP requests')
            ->value(fn() => (int) cache()->get('metrics.requests.4xx', 0));

        Prometheus::addGauge('http_requests_5xx')
            ->helpText('Server error HTTP requests')
            ->value(fn() => (int) cache()->get('metrics.requests.5xx', 0));

        Prometheus::addGauge('queue_jobs_pending')
            ->helpText('Pending queue jobs')
            ->value(fn() => \DB::table('jobs')->count());

        Prometheus::addGauge('queue_jobs_failed')
            ->helpText('Failed queue jobs')
            ->value(fn() => \DB::table('failed_jobs')->count());

        Prometheus::addGauge('mikrotik_routers_total')
            ->helpText('Total registered routers')
            ->value(fn() => \App\Models\Router::count());

        Prometheus::addGauge('mikrotik_routers_active')
            ->helpText('Active routers')
            ->value(fn() => \App\Models\Router::where('is_active', true)->count());
    }
}
