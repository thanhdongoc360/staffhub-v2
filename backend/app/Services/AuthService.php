<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $credentials)
    {
        if (
            !Auth::attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password']
            ])
        ) {

            return [
                'success' => false,
                'message' => 'Invalid credentials',
                'code' => 401
            ];
        }

        $user = Auth::user();

        $user->load('employee');

        $token = $user
            ->createToken('auth_token')
            ->plainTextToken; // lấy token dưới dạng chuỗi

        return [
            'success' => true,

            'data' => [
                'message' => 'Login success',
                'role' => $user->role,
                'user' => $user,
                'token' => $token
            ]
        ];
    }

    public function logout($user)
    {
        $user
            ->currentAccessToken()
            ->delete();
    }
}