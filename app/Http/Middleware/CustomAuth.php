<?php

namespace App\Http\Middleware;

use App\Models\Auth as AuthModel;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
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
            'session_data' => session('authenticated_user'),
            'has_remember_cookie' => $request->hasCookie('remember_token')
        ]);
        
        // Check if user is authenticated via our custom session
        if (!session()->has('authenticated_user')) {
            // Check for remember token as fallback
            if ($this->checkRememberToken($request)) {
                \Log::info('User authenticated via remember token');
                return $next($request);
            }
            
            \Log::info('No authenticated user session or remember token, redirecting to login');
            return redirect('/login');
        }

        \Log::info('User is authenticated via session, allowing access');
        return $next($request);
    }

    /**
     * Check if user has a valid remember token and authenticate them
     */
    private function checkRememberToken(Request $request)
    {
        $rememberToken = $request->cookie('remember_token');
        
        if (!$rememberToken) {
            return false;
        }

        $auth = AuthModel::where('remember_token', $rememberToken)->first();
        
        if (!$auth) {
            // Invalid token, remove the cookie
            Cookie::queue(Cookie::forget('remember_token'));
            return false;
        }

        // Get the user data
        $user = User::where('pn', $auth->pn)->first();
        
        if (!$user) {
            return false;
        }

        // Create session for the remembered user
        Session::put('authenticated_user', [
            'pn' => $user->pn,
            'name' => $user->name,
            'role' => $auth->role,
            'department_id' => $user->department_id,
        ]);

        return true;
    }
}
