<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Attempt to authenticate the user via the JWT token
            $user = JWTAuth::parseToken()->authenticate();
            
            // Check if the user has an admin role
            if ($user && $user->role_id == 1) { // admin role_id is 1 
                return $next($request);
            }

            // If not an admin, return a forbidden response
            return response()->json(['message' => 'Forbidden'], 403);

        } catch (JWTException $e) {
            // If the token is invalid or expired, return a response
            return response()->json(['message' => 'Token is invalid or expired'], 401);
        }
    }

}
