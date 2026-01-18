<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::guard('admin')->user();

        // Ensure user has admin or editor role
        if (! in_array($user->role, ['admin', 'editor'])) {
            Auth::guard('admin')->logout();

            return redirect()->route('admin.login')
                ->with('error', 'You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
