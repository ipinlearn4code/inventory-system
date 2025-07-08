<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth as AuthModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'pn' => 'required|string|max:8',
            'password' => 'required|string',
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
        Session::put('authenticated_user', [
            'pn' => $user->pn,
            'name' => $user->name,
            'role' => $auth->role,
            'department_id' => $user->department_id,
        ]);

        // Debug: let's first test if session is working
        Session::save(); // Force save session
        
        // Redirect to test page first to verify authentication
        return redirect('/admin');
    }

    public function logout(Request $request)
    {
        Session::forget('authenticated_user');
        Session::invalidate();
        Session::regenerateToken();

        return redirect('/login');
    }
}
