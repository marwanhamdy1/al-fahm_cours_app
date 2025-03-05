<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;


class HandelAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$token = $request->header('Authorization')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
  // Try to decode the token
  try {
    $user = JWTAuth::parseToken()->authenticate();

    // Check token expiration
    $expiration = JWTAuth::parseToken()->getPayload()->get('exp');
    if (time() > $expiration) {
        return response()->json(['error' => 'Token has expired'], 401);
    }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

        return $next($request);
    }
}