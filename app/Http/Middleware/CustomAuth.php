<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Debug: log what's happening
        \Log::info('CustomAuth middleware triggered', [
            'url' => $request->url(),
            'has_session' => session()->has('authenticated_user'),
            'session_data' => session('authenticated_user')
        ]);
        
        // Check if user is authenticated via our custom session
        if (!session()->has('authenticated_user')) {
            \Log::info('No authenticated user session, redirecting to login');
            return redirect('/login');
        }

        \Log::info('User is authenticated, allowing access');
        return $next($request);
    }
}
