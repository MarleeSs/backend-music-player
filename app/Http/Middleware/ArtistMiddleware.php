<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class ArtistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();
            $decode = JWT::decode($token, new Key(env('JWT_SECRET_KEY'), 'HS256'));
            $request->merge([
                'userId' => $decode->artistId,
                'role' => $decode->role,
            ]);
            return $decode->role == 'artist' ? $next($request) : response()->json('Access Denied', 401);
        } catch (ExpiredException $e) {
            return response()->json('Token Expired', 400);
        }
    }
}
