<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFileAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated (either via session or Sanctum)
        if (!auth()->check() && !auth('sanctum')->check()) {
            // Check session-based authentication as fallback
            $sessionUser = session('authenticated_user');
            if (!$sessionUser) {
                abort(403, 'Unauthorized access to file');
            }
        }

        return $next($request);
    }
}
