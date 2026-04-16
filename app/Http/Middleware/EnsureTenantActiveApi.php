<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * JSON variant of EnsureTenantActive for API routes.
 *
 * When an authenticated user's tenant has been deactivated we want to block
 * API traffic without wiping the session (which the web variant does).
 * Returns 403 with a structured JSON body that matches the rest of the API.
 */
final class EnsureTenantActiveApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant && ! $user->tenant->is_active) {
            return response()->json([
                'data' => null,
                'meta' => [
                    'error' => 'Your organization account has been deactivated. Please contact support.',
                    'code' => 'tenant_inactive',
                ],
            ], 403);
        }

        return $next($request);
    }
}
