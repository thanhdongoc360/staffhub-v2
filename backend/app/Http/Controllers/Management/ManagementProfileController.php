<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\ManagementProfileService;

class ManagementProfileController extends Controller
{
    public function __construct(
        private ManagementProfileService $profileService
    ) {}

    public function profile(Request $request)
    {
        return response()->json([
            'data' => $this->profileService
                ->getProfile(
                    $request->user()
                )
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20'
        ]);

        $this->profileService
            ->updateProfile(
                $request->user(),
                $data
            );

        return response()->json([
            'message' =>
                'Cập nhật hồ sơ thành công'
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6'
        ]);

        $result = $this->profileService
            ->changePassword(
                $request->user(),
                $data['current_password'],
                $data['new_password']
            );

        if (!$result['success']) {

            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' =>
                'Đổi mật khẩu thành công'
        ]);
    }
}