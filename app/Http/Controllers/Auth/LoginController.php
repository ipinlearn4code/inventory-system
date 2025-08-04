<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth as AuthModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Check for remember token first
        if ($this->checkRememberToken()) {
            return redirect('/admin');
        }
        
        // If user is already authenticated, redirect to admin
        if (session()->has('authenticated_user')) {
            return redirect('/admin');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'pn' => 'required|string|max:8',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        $auth = AuthModel::where('pn', $request->pn)->first();

        if (!$auth || !Hash::check($request->password, $auth->password)) {
            throw ValidationException::withMessages([
                'pn' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Get the user data
        $user = User::where('pn', $request->pn)->first();
        
        if (!$user) {
            throw ValidationException::withMessages([
                'pn' => ['User data not found.'],
            ]);
        }

        // Store user data in session
        $userData = [
            'pn' => $user->pn,
            'name' => $user->name,
            'role' => $auth->role,
            'department_id' => $user->department_id,
        ];
        
        Session::put('authenticated_user', $userData);

        // Handle remember me functionality
        if ($request->boolean('remember')) {
            $this->createRememberToken($auth);
        }

        // Debug: let's first test if session is working
        Session::save(); // Force save session
        
        // Redirect to test page first to verify authentication
        return redirect('/admin');
    }

    public function logout(Request $request)
    {
        // Clear remember token cookie if it exists
        if ($request->hasCookie('remember_token')) {
            Cookie::queue(Cookie::forget('remember_token'));
        }
        
        Session::forget('authenticated_user');
        Session::invalidate();
        Session::regenerateToken();

        return redirect('/login');
    }

    /**
     * Check if user has a valid remember token
     */
    private function checkRememberToken()
    {
        $rememberToken = request()->cookie('remember_token');
        
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

    /**
     * Create a remember token for the user
     */
    private function createRememberToken(AuthModel $auth)
    {
        $token = Str::random(60);
        
        // Update the auth record with the remember token
        $auth->update(['remember_token' => $token]);
        
        // Set cookie for 30 days
        Cookie::queue('remember_token', $token, 60 * 24 * 30);
    }
}
