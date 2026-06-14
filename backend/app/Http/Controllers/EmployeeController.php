<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmployeeService;

class EmployeeController extends Controller
{
    public function __construct(   
        private EmployeeService $employeeService
    ) {}

    public function profile(Request $request)
    {
        return response()->json([
            'data' => $this->employeeService
                ->getProfile($request->user())
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'email' => 'required|email'
        ]);

        $this->employeeService
            ->updateProfile($request->user(), $data);

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6'
        ]);

        $changed = $this->employeeService
            ->changePassword($request->user(), $data);

        if (!$changed) {
            return response()->json([
                'message' => 'Mật khẩu hiện tại không đúng'
            ], 422);
        }

        return response()->json([
            'message' => 'Đổi mật khẩu thành công'
        ]);
    }
}