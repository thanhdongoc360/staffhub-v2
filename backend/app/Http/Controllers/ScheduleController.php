<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScheduleService;

class ScheduleController extends Controller
{
    public function __construct(
        private ScheduleService $scheduleService
    ) {}

    public function mySchedule(Request $request)
    {  
        $result = $this->scheduleService
            ->getMySchedule(
                auth()->user(),
                $request->type ?? 'week'
            );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json($result['data']);
    }

    public function getShifts()
    {
        return response()->json(
            $this->scheduleService->getShifts()
        );
    }

    public function storeShift(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'start_time' => 'required',
            'end_time' => 'required'
        ]);

        return response()->json(
            $this->scheduleService->createShift($data)
        );
    }

    public function updateShift(
        Request $request,
        int $id
    ) {
        return response()->json(
            $this->scheduleService
                ->updateShift($id, $request->all())
        );
    }

    public function deleteShift(int $id)
    {
        $this->scheduleService->deleteShift($id);

        return response()->json([
            'message' => 'Deleted'
        ]);
    }

    public function assignEmployees(Request $request)
    {
        $data = $request->validate([
            'schedule_id' => 'required|integer|exists:work_schedules,id',
            'shift_id' => 'required|integer|exists:work_shifts,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:employees,id',
            'dates' => 'required|array|min:1',
            'dates.*' => 'date'
        ]);

        $result = $this->scheduleService
            ->assignEmployees($data);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Assigned'
        ]);
    }

    public function managementView(Request $request)
    {
        return response()->json(
            $this->scheduleService
                ->getDepartmentSchedules(
                    auth()->user()
                )
        );
    }

    public function createWeekSchedule(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date'
        ]);   

        $result = $this->scheduleService->createWeekSchedule(
            $request->start_date
        );

        return response()->json($result);
    }

    public function currentWeekSchedule()
    {
        return response()->json(
            $this->scheduleService->getLatestWeekSchedule()
        );
    }
}