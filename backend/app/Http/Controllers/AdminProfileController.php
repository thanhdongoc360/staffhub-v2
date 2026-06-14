<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdminProfileService;

class AdminProfileController extends Controller
{
    public function __construct(
        private AdminProfileService $adminProfileService
    ) {}

    public function profile(Request $request)
    {
        return response()->json([
            'data' => $this->adminProfileService
                ->getProfile($request->user())
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20'
        ]);

        $this->adminProfileService
            ->updateProfile(
                $request->user(),
                $data
            );

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        $result = $this->adminProfileService
            ->changePassword(
                $request->user(),
                $data
            );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Đổi mật khẩu thành công'
        ]);
    }
}