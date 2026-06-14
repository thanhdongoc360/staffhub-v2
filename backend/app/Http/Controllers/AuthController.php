<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}   

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $result = $this->authService
            ->login($credentials);

        if (!$result['success']) {

            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json(
            $result['data']
        );
    }

    public function logout(Request $request)
    {
        $this->authService
            ->logout(
                $request->user()
            );

        return response()->json([
            'message' =>
                'Logged out successfully'
        ]);
    }
}