<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    /**
     * Ensure the authenticated user's tenant is active.
     * If not, log out the user and redirect to login with an error.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant && ! $user->tenant->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your organization account has been deactivated. Please contact support.',
            ]);
        }

        return $next($request);
    }
}
