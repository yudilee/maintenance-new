<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = null;

        // Step 1: Try local database first (null auth_source treated as 'local')
        $localUser = User::where('email', $request->email)->first();
        
        $isLocalUser = $localUser && (
            $localUser->auth_source === 'local' || 
            $localUser->auth_source === null || 
            $localUser->auth_source === ''
        );
        
        if ($isLocalUser && Hash::check($request->password, $localUser->password)) {
            $user = $localUser;
        }

        // If user found and authenticated
        if ($user) {
            // Check if 2FA is enabled
            if ($user->two_factor_enabled) {
                // Store user ID in session for 2FA challenge
                $request->session()->put('2fa_user_id', $user->id);
                $request->session()->put('2fa_remember', $request->boolean('remember'));
                return redirect()->route('2fa.challenge');
            }
            
            // No 2FA - complete login
            $this->completeLogin($user, $request);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }


    /**
     * Complete login and record session
     */
    protected function completeLogin($user, Request $request): void
    {
        Auth::login($user, $request->boolean('remember') ?? session('2fa_remember', false));
        $request->session()->regenerate();
        
        // Record session for session management
        \App\Models\UserSession::recordLogin(
            $user->id,
            session()->getId(),
            $request->ip(),
            $request->userAgent()
        );
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'auth_source' => 'local', // Internal database user
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        // Mark the current session as logged out
        \App\Models\UserSession::where('session_id', session()->getId())
            ->update(['is_current' => false]);
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
