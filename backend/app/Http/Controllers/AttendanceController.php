<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    public function checkIn()
    {
        $result = $this->attendanceService  
            ->checkIn(auth()->user());  

        // Nếu check-in không thành công, trả về lỗi với mã lỗi và thông báo
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        // Nếu check-in thành công, trả về dữ liệu điểm danh mới nhất
        return response()->json(
            $result['data']
        );
    }

    public function checkOut()
    {
        $result = $this->attendanceService
            ->checkOut(auth()->user());

        // $result['success']: cách lấy giá trị của key 'success' trong mảng $result
        // Nếu check-out không thành công, trả về lỗi với mã lỗi và thông báo (chưa check-in hoặc đã check-out rồi)
        if (!$result['success']) {

            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        // Nếu check-out thành công, trả về dữ liệu điểm danh đã được cập nhật
        return response()->json(
            $result['data']    
        );
    }

    public function myAttendance(Request $request)
    {
        return response()->json(
            $this->attendanceService
                ->getMyAttendance(
                    auth()->user(),
                    $request->only([
                        'month',
                        'year'
                    ])
                )
        );
    }

    public function managementIndex(Request $request)
    {
        $result = $this->attendanceService
            ->getManagementAttendance(
                auth()->user(),
                $request->only([
                    'month',
                    'year',
                    'status',
                    'search'
                ])
            );

        if (!$result['success']) {

            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json(
            $result['data']
        );
    }

    public function update(
        Request $request,
        int $id
    ) {
        $data = $request->validate([
            'check_in_time' => 'nullable|date',
            'check_out_time' => 'nullable|date',
            'status' => 'nullable|string',
            'note' => 'nullable|string'
        ]);

        $result = $this->attendanceService
            ->updateAttendance(
                auth()->user(),
                $id,
                $data
            );

        if (!$result['success']) {

            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json(
            $result['data']
        );
    }
}