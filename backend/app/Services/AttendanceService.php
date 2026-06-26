<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceRule;
use Carbon\Carbon;

class AttendanceService
{
    public function checkIn($user)
    {
        $employee = $user->employee;

        $today = Carbon::today();

        // Kiểm tra nếu đã có bản ghi điểm danh cho ngày hôm nay và đã check-in
        $exists = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($exists && $exists->check_in_time) {
            return [
                'success' => false,
                'message' => 'Bạn đã check-in rồi',
                'code' => 400
            ];
        }

        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $today
            ],
            [
                'check_in_time' => now(),
                'status' => 'pending'
            ]
        );

        return [
            'success' => true,
            'data' => $attendance
        ];
    }

    public function checkOut($user)
    {
        $employee = $user->employee;

        $today = Carbon::today();

        $attendance = Attendance::where(
            'employee_id',
            $employee->id
        )
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return [
                'success' => false,
                'message' => 'Bạn chưa check-in',
                'code' => 400
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'success' => false,
                'message' => 'Bạn đã check-out rồi',
                'code' => 400
            ];
        }

        $attendance->check_out_time = now();

        $workingMinutes = Carbon::parse(
            $attendance->check_in_time
        )
            ->diffInMinutes(
                $attendance->check_out_time
            );

        $rule = AttendanceRule::first();

        $attendance->working_minutes =
            $workingMinutes;

        // overtime
        if (
            $workingMinutes >
            $rule->standard_work_minutes
        ) {
            $attendance->overtime_minutes =
                $workingMinutes -
                $rule->standard_work_minutes;
        }

        // status
        $checkIn = Carbon::parse(
            $attendance->check_in_time
        );

        $startTime = Carbon::parse(
            $rule->work_start_time
        );

        $minValidWorkMinutes = 60;

        if ($workingMinutes < $minValidWorkMinutes) {
            $attendance->status = 'pending';
            $attendance->note = 'Thời gian làm việc quá ngắn, cần quản lý xác nhận';
        } elseif ($workingMinutes < $rule->half_day_threshold_minutes) {
            $attendance->status = 'pending';
            $attendance->note = 'Thời gian làm việc chưa đủ nửa ngày, cần quản lý xác nhận';
        } elseif ($workingMinutes < $rule->standard_work_minutes) {
            $attendance->status = 'half_day';
        } elseif ($checkIn->gt($startTime)) {
            $lateMinutes = $startTime->diffInMinutes($checkIn);

            if ($lateMinutes <= $rule->late_threshold_minutes) {
                $attendance->status = 'late';
            } else {
                $attendance->status = 'half_day';
            }
        } else {
            $attendance->status = 'present';
        }

        $attendance->save();

        return [
            'success' => true,
            'data' => $attendance
        ];
    }

    public function getMyAttendance(
        $user,
        array $filters = []
    ) {
        $employee = $user->employee;

        $query = Attendance::where(
            'employee_id',
            $employee->id
        );

        if (!empty($filters['month'])) {
            $query->whereMonth(
                'date',
                $filters['month']
            );
        }

        if (!empty($filters['year'])) {
            $query->whereYear(
                'date',
                $filters['year']
            );
        }

        return $query
            ->orderByDesc('date')
            ->get();
    }

    public function getManagementAttendance(
        $user,
        array $filters = []
    ) {
        if (!$user->employee) {
            return [
                'success' => false,
                'message' => 'No employee',
                'code' => 400
            ];
        }

        $department = $user->employee->department;

        $query = Attendance::query()
            ->join(
                'employees',
                'attendances.employee_id',
                '=',
                'employees.id'
            )
            ->join(
                'users',
                'employees.user_id',
                '=',
                'users.id'
            )
            ->where(
                'employees.department',
                $department
            )
            ->select(
                'attendances.*',
                'users.name',
                'employees.employee_code'
            );

        if (!empty($filters['month'])) {
            $query->whereMonth(
                'date',
                $filters['month']
            );
        }

        if (!empty($filters['year'])) {
            $query->whereYear(
                'date',
                $filters['year']
            );
        }

        if (!empty($filters['status'])) {
            $query->where(
                'attendances.status',
                $filters['status']
            );
        }

        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->where(function ($q) use ($search) {

                $q->where(
                    'users.name',
                    'like',
                    "%$search%"
                )
                    ->orWhere(
                        'employees.employee_code',
                        'like',
                        "%$search%"
                    );
            });
        }

        return [
            'success' => true,
            'data' => $query
                ->orderByDesc('date')
                ->get()
        ];
    }

    public function updateAttendance(
        $user,
        int $id,
        array $data
    ) {
        if ($user->role !== 'management') {
            return [
                'success' => false,
                'message' => 'Forbidden',
                'code' => 403
            ];
        }

        $attendance = Attendance::findOrFail($id);

        $attendance->check_in_time =
            $data['check_in_time'];

        $attendance->check_out_time =
            $data['check_out_time'];

        $attendance->status =
            $data['status'];

        $attendance->note =
            $data['note'];

        if (
            $attendance->check_in_time &&
            $attendance->check_out_time
        ) {
            $attendance->working_minutes =
                Carbon::parse(
                    $attendance->check_in_time
                )->diffInMinutes(
                    $attendance->check_out_time
                );
        }

        $attendance->save();

        return [
            'success' => true,
            'data' => $attendance
        ];
    }
}
