<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class LoginController extends BaseController
{
    /**
     * Show the admin login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        // Throttle login attempts
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if too many login attempts
        $key = 'login.attempts.'.$request->ip();
        $attempts = Cache::get($key, 0);

        if ($attempts >= 5) {
            $seconds = Cache::get('login.lockout.'.$request->ip(), 0);
            if ($seconds > 0) {
                throw ValidationException::withMessages([
                    'email' => ['Too many login attempts. Please try again in '.$seconds.' seconds.'],
                ]);
            }
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            // Clear login attempts on success
            Cache::forget($key);
            Cache::forget('login.lockout.'.$request->ip());

            $request->session()->regenerate();

            $user = Auth::guard('admin')->user();

            if (! in_array($user->role, ['admin', 'editor'])) {
                Auth::guard('admin')->logout();
                throw ValidationException::withMessages([
                    'email' => ['You do not have permission to access the admin panel.'],
                ]);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        // Increment login attempts
        $attempts++;
        Cache::put($key, $attempts, now()->addMinutes(15));

        if ($attempts >= 5) {
            Cache::put('login.lockout.'.$request->ip(), 300, now()->addMinutes(5)); // 5 minute lockout
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
