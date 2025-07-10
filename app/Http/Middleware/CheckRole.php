<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->auth) {
            return response()->json([
                'message' => 'Unauthorized access.',
                'errorCode' => 'ERR_UNAUTHORIZED'
            ], 401);
        }

        $userRole = $user->auth->role;
        
        // Convert superadmin to admin for mobile API access
        if ($userRole === 'superadmin') {
            $userRole = 'admin';
        }
        
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Insufficient permissions.',
                'errorCode' => 'ERR_INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        return $next($request);
    }
}
