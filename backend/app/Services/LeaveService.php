<?php

namespace App\Services;

use App\Models\LeaveRequest;

class LeaveService
{
    public function createLeave($user, array $data)
    {
        if (!$user || !$user->employee) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy thông tin nhân viên',
                'code' => 422
            ];
        }

        $leave = LeaveRequest::create([
            'employee_id' => $user->employee->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'type' => $data['type'],
            'reason' => $data['reason'],
            'status' => 'Chờ duyệt'
        ]);

        return [
            'success' => true,
            'data' => $leave
        ];
    }

    public function getEmployeeLeaves($user)
    {
        if (!$user || !$user->employee) {
            return null;
        }

        return LeaveRequest::where(
                'employee_id',
                $user->employee->id
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function listAllLeaves(array $params = [])
    {
        $query = LeaveRequest::with('employee.user');

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('employee.user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 10;

        $query = $query->orderBy('created_at', 'desc');

        if ($perPage && $perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function getAllLeaves()
    {
        return LeaveRequest::with('employee.user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
    }

    public function listDepartmentLeaves($user, array $params = [])
    {
        if (!$user->employee) {
            return null;
        }

        $department = $user->employee->department;
        $query = LeaveRequest::with('employee.user')
            ->whereHas('employee', function ($q) use ($department) {
                $q->where('department', $department);
            });

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('employee.user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 10;

        $query = $query->orderBy('created_at', 'desc');

        if ($perPage && $perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function getDepartmentLeaves($user)
    {
        if (!$user->employee) {
            return null;
        }

        $department = $user->employee->department;

        return LeaveRequest::with('employee.user')
            ->whereHas('employee', function ($query) use ($department) {
                $query->where('department', $department);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function approveLeave($user, int $id)
    {
        return $this->handleLeaveStatus(
            $user,
            $id,
            'Đã duyệt'
        );
    }

    public function rejectLeave($user, int $id)
    {
        return $this->handleLeaveStatus(
            $user,
            $id,
            'Từ chối'
        );
    }

    private function handleLeaveStatus($user, int $id, string $status)
    {
        if (!$user->employee) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy thông tin nhân viên',
                'code' => 404
            ];
        }

        $department = $user->employee->department;

        $leave = LeaveRequest::where('id', $id)
            ->whereHas('employee', function ($query) use ($department) {
                $query->where('department', $department);
            })
            ->first();

        if (!$leave) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền xử lý đơn này',
                'code' => 403
            ];
        }

        if ($leave->status !== 'Chờ duyệt') {
            return [
                'success' => false,
                'message' => 'Đơn này đã được xử lý',
                'code' => 400
            ];
        }

        $leave->update([
            'status' => $status
        ]);

        return [
            'success' => true
        ];
    }
}