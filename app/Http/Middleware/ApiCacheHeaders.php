<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add cache headers for offline support on specific endpoints
        if ($request->is('api/v1/user/home/summary') || 
            $request->is('api/v1/user/devices') || 
            $request->is('api/v1/user/profile')) {
            
            $response->header('Cache-Control', 'public, max-age=300'); // 5 minutes
            $response->header('ETag', md5($response->getContent()));
        }

        return $response;
    }
}
