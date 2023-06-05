<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
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
                'message' => 'Successful login',
                'statusCode' => 200,
                'data' => [
                    'fullName' => Auth::user()->full_name,
                    'userId' => Auth::user()->id,
                    'role' => Auth::user()->role
                ],
                'token' => $token
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid email or password'
        ], 401);
    }

    public function register(Request $request): JsonResponse
    {
        try {
            $v = Validator::make($request->all(), [
                'fullName' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string',
                'role' => 'required|string',
            ]);

            if ($v->fails()) {
                return response()->json([
                    'message' => $v->errors()->first(),
                    'statusCode' => 400,
                ], 400);
            }

            // get if user already exists
            $user = User::where('email', $request->email)->first();
            if ($user) {
                return response()->json([
                    'message' => 'Email already exists',
                    'statusCode' => 400,
                ], 400);
            }

            $user = new User();
            $user->full_name = $request->fullName;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->password = $request->password;
            $user->save();

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
