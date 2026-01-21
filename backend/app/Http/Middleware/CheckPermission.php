<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$permissions
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => ['authentication' => ['You must be logged in to access this resource.']],
            ], 401);
        }

        // Webadmin bypasses all permission checks
        if ($user->isWebadmin()) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        if (!$user->hasAnyPermission($permissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'errors' => [
                    'authorization' => ['You do not have the required permissions to perform this action.'],
                    'required_permissions' => $permissions,
                ],
            ], 403);
        }

        return $next($request);
    }
}
