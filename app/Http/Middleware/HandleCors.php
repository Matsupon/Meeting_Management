<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $origin = $request->header('Origin');
        $allowedOrigins = config('app.env') === 'production'
            ? array_map('trim', explode(',', config('app_cors.allowed_origins', config('app.url', 'http://localhost'))))
            : [$origin ?? '*'];

        $allowOrigin = '*';
        if ($origin && (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins))) {
            $allowOrigin = $origin;
        }

        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-CSRF-TOKEN, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
