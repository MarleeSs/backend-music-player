<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();
            $decode = JWT::decode($token, new Key(env('JWT_SECRET_KEY'), 'HS256'));
            return $decode->role == 'user' ? $next($request) : response()->json('Access Denied', 401);
        } catch (ExpiredException $e) {
            return response()->json('Token Expired', 400);
        }
    }
}
