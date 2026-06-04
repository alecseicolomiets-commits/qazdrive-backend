<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'errors'  => [
                    'email' => ['Email или пароль неверны'],
                ],
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is disabled',
                'errors'  => [
                    'email' => ['Аккаунт заблокирован'],
                ],
            ], 403);
        }

        $token = JWTAuth::fromUser($user);

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 200);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'nullable|string|max:20|unique:users,phone',
            'city'      => 'nullable|string|max:100',
            'password'  => 'required|string|min:8',
        ], [
            'full_name.required' => 'Полное имя обязательно',
            'email.required'     => 'Email обязателен',
            'email.unique'       => 'Email уже зарегистрирован в системе',
            'phone.unique'       => 'Номер телефона уже используется',
            'password.min'       => 'Пароль должен быть не менее 8 символов',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fullName = $request->full_name;
        $nickname = explode(' ', trim($fullName))[0];

        $user = User::create([
            'full_name' => $fullName,
            'nickname'  => $nickname,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'city'      => $request->city,
            'password'  => Hash::make($request->password),
            'role'      => 'user',
            'is_active' => true,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // токен уже невалиден — не страшно
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'nickname'   => $user->nickname,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'city'       => $user->city,
            'avatar_url' => $user->avatar_url,
            'role'       => $user->role,
        ];
    }
}