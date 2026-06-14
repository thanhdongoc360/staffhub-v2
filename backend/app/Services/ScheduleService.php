<?php

namespace App\Services;

use App\Models\EmployeeScheduleAssignment;
use App\Models\WorkShift;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class ScheduleService
{
    public function getMySchedule($user, string $type = 'week')
    {
        $employee = $user->employee;

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'No employee',
                'code' => 400
            ];
        }

        $query = EmployeeScheduleAssignment::with('shift')
            ->where('employee_id', $employee->id);

        if ($type === 'today') {
            $query->whereDate(
                'work_date',
                Carbon::today()
            );
        }

        if ($type === 'week') {
            $query->whereBetween('work_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        }

        if ($type === 'month') {
            $query->whereMonth(
                'work_date',
                Carbon::now()->month
            )->whereYear(
                'work_date',
                Carbon::now()->year
            );
        }

        return [
            'success' => true,
            'data' => $query
                ->orderBy('work_date')
                ->get()
        ];
    }

    public function getShifts()
    {
        return WorkShift::orderBy('id', 'desc')->get();
    }

    public function createShift(array $data)
    {
        return WorkShift::create($data);
    }

    public function updateShift(int $id, array $data)
    {
        $shift = WorkShift::findOrFail($id);

        $shift->update($data);

        return $shift;
    }

    public function deleteShift(int $id)
    {
        WorkShift::findOrFail($id)->delete();
    }

    public function assignEmployees(array $data)
    {
        $schedule = WorkSchedule::find($data['schedule_id']);

        if (!$schedule) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy lịch tuần',
                'code' => 404
            ];
        }

        $dates = collect($data['dates'])
            ->map(fn ($date) => Carbon::parse($date)->startOfDay())
            ->sort()
            ->values();

        $firstDate = $dates->first();
        $lastDate = $dates->last();

        $scheduleStart = Carbon::parse($schedule->start_date)->startOfDay();
        $scheduleEnd = Carbon::parse($schedule->end_date)->endOfDay();

        if ($firstDate->lt($scheduleStart) || $lastDate->gt($scheduleEnd)) {
            $matchedSchedule = WorkSchedule::query()
                ->whereDate('start_date', '<=', $firstDate->toDateString())
                ->whereDate('end_date', '>=', $lastDate->toDateString())
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->first();

            if (!$matchedSchedule) {
                return [
                    'success' => false,
                    'message' => 'Ngày làm việc chưa nằm trong lịch tuần đã tạo',
                    'code' => 422
                ];
            }

            $schedule = $matchedSchedule;
            $scheduleStart = Carbon::parse($schedule->start_date)->startOfDay();
            $scheduleEnd = Carbon::parse($schedule->end_date)->endOfDay();
        }

        foreach ($dates as $workDate) {

            if ($workDate->lt($scheduleStart) || $workDate->gt($scheduleEnd)) {
                return [
                    'success' => false,
                    'message' => 'Ngày làm việc chưa nằm trong lịch tuần đã tạo',
                    'code' => 422
                ];
            }
        }

        foreach ($data['employee_ids'] as $employeeId) {

            foreach ($data['dates'] as $date) {

                $exists = EmployeeScheduleAssignment::where([
                    'employee_id' => $employeeId,
                    'work_date' => $date
                ])->exists();

                if (!$exists) {

                    EmployeeScheduleAssignment::create([
                        'employee_id' => $employeeId,
                        'work_schedule_id' => $schedule->id,
                        'work_shift_id' => $data['shift_id'],
                        'work_date' => $date,
                    ]);
                }
            }
        }

        return [
            'success' => true
        ];
    }

    public function getDepartmentSchedules($user)
    {
        $department = $user->employee->department;

        return EmployeeScheduleAssignment::with([
                'shift',
                'employee.user'
            ])
            ->whereHas('employee', function ($q) use ($department) {
                $q->where('department', $department);
            })
            ->orderBy('work_date')
            ->get();
    }

    public function createWeekSchedule(string $startDate)
    {
        $start = Carbon::parse($startDate)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $schedule = WorkSchedule::firstOrCreate(
            [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            [
                'status' => 'draft'
            ]
        );

        return [
            'schedule' => $schedule,
            'created' => $schedule->wasRecentlyCreated,
        ];
    }

    public function getLatestWeekSchedule()
    {
        $today = Carbon::today();

        $currentSchedule = WorkSchedule::query()
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $today->toDateString())
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();

        if ($currentSchedule) {
            return $currentSchedule;
        }

        return WorkSchedule::orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();
    }
}