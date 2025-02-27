<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Type\Integer;
use Symfony\Component\HttpFoundation\Response;


class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roleName)
    {
        $user = Auth::user();

        // Check if the user's role_id matches the required role_id
        if ($user && $user->role_name == $roleName) {
            return $next($request);
        }

        // If not, return unauthorized response
        return response()->json(['message' => 'Unauthorized'], 403);
    }

}
