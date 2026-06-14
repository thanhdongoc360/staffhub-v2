<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Models\LeaveRequest;

class ManagementEmployeeService
{
    public function getEmployees(
        $management,
        array $filters = []
    )
    {
        $department =
            $management->employee->department;

        $query = Employee::with('user')
            ->where(
                'department',
                $department
            );

        // search
        if (!empty($filters['search'])) {

            $keyword =
                '%' . $filters['search'] . '%';

            $query->where(function ($q) use ($keyword) {

                $q->where(
                    'employee_code',
                    'like',
                    $keyword
                )
                ->orWhereHas(
                    'user',
                    function ($userQuery)
                    use ($keyword) {

                        $userQuery
                            ->where(
                                'name',
                                'like',
                                $keyword
                            )
                            ->orWhere(
                                'email',
                                'like',
                                $keyword
                            );
                    }
                );
            });
        }

        // status
        if (!empty($filters['status'])) {

            $query->where(
                'status',
                $filters['status']
            );
        }

        // sort
        $sortBy =
            $filters['sort_by'] ?? 'id';

        $sortOrder =
            $filters['sort_order'] ?? 'desc';

        if ($sortBy === 'name') {

            $query->join(
                    'users',
                    'employees.user_id',
                    '=',
                    'users.id'
                )
                ->orderBy(
                    'users.name',
                    $sortOrder
                )
                ->select('employees.*');

        } else {

            $query->orderBy(
                $sortBy,
                $sortOrder
            );
        }

        return [
            'department' => $department,

            'employees' => $query->paginate(15)
        ];
    }

    public function showEmployee(
        $management,
        int $id
    )
    {
        $department =
            $management->employee->department;

        return Employee::with('user')
            ->where(
                'department',
                $department
            )
            ->findOrFail($id);
    }

    public function getDashboard($user)
    {
        if (!$user->employee) {

            return [
                'success' => false,
                'message' =>
                    'Không có thông tin nhân viên',
                'code' => 403
            ];
        }

        $department =
            $user->employee->department;

        $totalUsers = User::whereHas(
            'employee',
            function ($q) use ($department) {

                $q->where(
                    'department',
                    $department
                );
            }
        )->count();

        $recentUsers = User::with('employee')
            ->whereHas(
                'employee',
                function ($q) use ($department) {

                    $q->where(
                        'department',
                        $department
                    );
                }
            )
            ->latest()
            ->take(5)
            ->get();

        $pendingLeaves = LeaveRequest::where(
                'status',
                'Chờ duyệt'
            )
            ->whereHas(
                'employee',
                function ($q) use ($department) {

                    $q->where(
                        'department',
                        $department
                    );
                }
            )
            ->count();

        return [
            'success' => true,

            'data' => [
                'total' => $totalUsers,
                'users' => $recentUsers,
                'pending_leaves' => $pendingLeaves
            ]
        ];
    }
}