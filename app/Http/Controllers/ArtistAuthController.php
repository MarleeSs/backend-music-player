<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ArtistAuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function artistAuth(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:artists,email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $artist = Artist::where('email', $validator->validate()['email'])->first();

        $payload = [
            'artistId' => $artist['id'],
            'role' => 'artist',
            'iat' => now()->timestamp,
            'exp' => now()->timestamp + 172000000
        ];

        $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

        return response()->json([
            'data' => [
                'message' => 'Successful login',
                'fullName' => $artist['full_name'],
                'artistId' => $artist['id'],
                'role' => 'artist'
            ],
            'token' => "{$token}"
        ], 200);
    }

}
