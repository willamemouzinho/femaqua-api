<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(AuthRegisterRequest $request) : JsonResponse
    {
        $userData = $request->validated();
        $hasEmail = User::select('id')->where("email", $userData["email"])->first();

        if ($hasEmail) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'The email provided already exists in our records.',
                ],
            ], 422);
        }

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => $userData['password'],
        ]);
        $access_token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'User registered successfully.',
            ],
            'user' => new UserResource($user),
            'access_token' => $access_token,
        ], 201);
    }

    public function login(AuthLoginRequest $request) : JsonResponse
    {
        $userData = $request->validated();
        $user = User::select('id', 'name', 'email', 'password', 'created_at')->where("email", $userData["email"])->first();

        if (! $user || ! Hash::check($userData["password"], $user->password)) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'message' => 'The provided credentials do not match our records.',
                ],
            ], 401);
        }

        $access_token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Login successful.',
            ],
            'user' => new UserResource($user),
            'access_token' => $access_token,
        ], 200);
    }

    public function logout(Request $request) : JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'message' => 'Logout successful.',
            ],
        ], 200);
    }
}
