<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Skip caching for authenticated admin routes
        if ($request->is('admin/*')) {
            return $next($request);
        }

        // Create cache key based on URL and query parameters
        $cacheKey = 'response_cache_' . md5($request->fullUrl());

        // Try to get cached response
        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            
            return response($cachedData['content'])
                ->withHeaders($cachedData['headers'])
                ->setStatusCode($cachedData['status']);
        }

        // Get fresh response
        $response = $next($request);

        // Only cache successful responses
        if ($response->getStatusCode() === 200) {
            $cacheData = [
                'content' => $response->getContent(),
                'headers' => $response->headers->all(),
                'status' => $response->getStatusCode(),
            ];

            Cache::put($cacheKey, $cacheData, $ttl);
        }

        return $response;
    }
}
