<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LeaveService;

class LeaveController extends Controller
{
    public function __construct(
        private LeaveService $leaveService
    ) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|string',
            'reason' => 'required|string'
        ]);

        $result = $this->leaveService
            ->createLeave($request->user(), $data);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Tạo đơn nghỉ phép thành công',
            'data' => $result['data']
        ]);
    }

    public function index(Request $request)
    {
        $leaves = $this->leaveService  
            ->getEmployeeLeaves($request->user());
  
        if (!$leaves) {
            return response()->json([
                'message' => 'Không tìm thấy nhân viên'
            ], 404);
        }
  
        return response()->json([
            'data' => $leaves
        ]);
    }

    public function adminIndex(Request $request)
    {
        $leaves = $this->leaveService->listAllLeaves($request->all());

        if ($leaves instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return response()->json([
                'data' => $leaves
            ]);
        }

        return response()->json([
            'data' => [
                'data' => $leaves,
                'total' => count($leaves),
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10
            ]
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $result = $this->leaveService
            ->approveLeave($request->user(), $id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Duyệt đơn thành công'
        ]);
    }

    public function reject(Request $request, int $id)
    {
        $result = $this->leaveService
            ->rejectLeave($request->user(), $id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Từ chối thành công'
        ]);
    }

    public function managementIndex(Request $request)
    {
        $leaves = $this->leaveService->listDepartmentLeaves(
            $request->user(),
            $request->all()
        );

        if (!$leaves) {
            return response()->json([
                'message' => 'Không tìm thấy thông tin nhân viên'
            ], 404);
        }

        if ($leaves instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return response()->json([
                'data' => $leaves
            ]);
        }

        return response()->json([
            'data' => [
                'data' => $leaves,
                'total' => count($leaves),
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10
            ]
        ]);
    }

    public function managementApprove(Request $request, int $id)
    {
        $result = $this->leaveService
            ->approveLeave($request->user(), $id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Duyệt đơn thành công'
        ]);
    }

    public function managementReject(Request $request, int $id)
    {
        $result = $this->leaveService
            ->rejectLeave($request->user(), $id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Từ chối thành công'
        ]);
    }
}