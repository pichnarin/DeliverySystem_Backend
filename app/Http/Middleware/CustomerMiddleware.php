<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Authenticate user
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Authentication failed'], 401);
            }
            // \Log::info('Authenticated User:', ['id' => $user->id, 'role_id' => $user->role_id]);

            // Ensure the user is a customer
            if ($user->role_id == 2) {
                return $next($request);
            }

            return response()->json(['message' => 'Forbidden, you are not authenticated to use this route'], 403);

        } catch (JWTException $e) {
            // \Log::error('JWT Authentication Error: ' . $e->getMessage());

            return response()->json(['message' => 'Token is invalid or expired'], 401);
        }
    }

}
