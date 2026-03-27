<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // Add context that appears on every log entry for this request
        Log::withContext([
            'request_id' => uniqid(),           // unique ID per request
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
            'ip'         => $request->ip(),
            'user_id'    => $request->user()?->id,
            'route'      => $request->route()?->getName(),
        ]);

        return $next($request);
    }
}