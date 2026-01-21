<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => ['authentication' => ['You must be logged in to access this resource.']],
            ], 401);
        }

        // Ensure role relationship is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Check if user has any of the required roles
        if (!in_array($user->role->slug, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'errors' => [
                    'authorization' => ['You do not have permission to perform this action.'],
                    'required_roles' => $roles,
                    'your_role' => $user->role->slug,
                ],
            ], 403);
        }

        return $next($request);
    }
}
