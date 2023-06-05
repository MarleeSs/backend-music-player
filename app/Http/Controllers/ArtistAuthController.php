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
            'message' => 'Successful login',
            'statusCode' => 200,
            'data' => [
                'fullName' => $artist['full_name'],
                'artistId' => $artist['id'],
                'role' => 'artist'
            ],
            'token' => $token
        ], 200);
    }

    public function artistRegister(Request $request): JsonResponse
    {
        try {
            $v = Validator::make($request->all(), [
                'fullName' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string',
                'role' => 'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg',
            ]);

            if ($v->fails()) {
                return response()->json([
                    'message' => $v->errors()->first(),
                    'statusCode' => 400,
                ], 400);
            }

            // get if artist already exists
            $artist = Artist::where('email', $request->email)->first();
            if ($artist) {
                return response()->json([
                    'message' => 'Email already exists',
                    'statusCode' => 400,
                ], 400);
            }

            $image = $request->file('image');
            $fileName = now()->timestamp . '_' . $request->image->getClientOriginalName();
            $image->move('uploads', $fileName);

            $artist = new Artist();
            $artist->full_name = $request->fullName;
            $artist->image = 'uploads/' . $fileName;
            $artist->email = $request->email;
            $artist->password = $request->password;
            $artist->role = $request->role;
            $artist->save();

            return response()->json([
                'message' => 'Register successful',
                'statusCode' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Register failed',
                'statusCode' => 500,
            ], 500);
        }
    }

}
