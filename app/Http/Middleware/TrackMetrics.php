<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Prometheus\Facades\Prometheus;
use Symfony\Component\HttpFoundation\Response;

class TrackMetrics
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $status = $response->getStatusCode();

        // Increment request counter by status group
        if ($status >= 500) {
            cache()->increment('metrics.requests.5xx');
        } elseif ($status >= 400) {
            cache()->increment('metrics.requests.4xx');
        } else {
            cache()->increment('metrics.requests.2xx');
        }

        return $response;
    }
}