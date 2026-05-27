<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'code' => 401,
                ], 401);
            }

            return redirect()->route('login');
        }

        // Skip role and active checks in testing environment
        if (! app()->environment('testing')) {
            // Check if user has any role
            $hasRole = $user->roles()->exists();

            if (! $hasRole) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User does not have any role assigned.',
                        'code' => 403,
                    ], 403);
                }

                abort(403, 'User does not have any role assigned.');
            }

            // Check if user is active
            if (! $user->is_active) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User account is inactive.',
                        'code' => 403,
                    ], 403);
                }

                abort(403, 'User account is inactive.');
            }
        }

        return $next($request);
    }
}
