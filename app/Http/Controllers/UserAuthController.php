<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserAuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function userAuth(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            try {
                return messageError($validator->messages()->toArray());
            } catch (\Exception $e) {
            }
        }

        if (Auth::attempt($validator->validate())) {
            $payload = [
                'userId' => Auth::user()->id,
                'role' => Auth::user()->role,
                'iat' => now()->timestamp,
                'exp' => now()->timestamp + 17200000000
            ];

            $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

            return response()->json([
                'data' => [
                    'message' => 'Successful login',
                    'fullName' => Auth::user()->full_name,
                    'userId' => Auth::user()->id,
                    'role' => Auth::user()->role
                ],
                'token' => "{$token}"
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid email or password'
        ], 401);
    }
}
