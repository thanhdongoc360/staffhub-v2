<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SalaryService;

class SalaryController extends Controller
{
    public function __construct(
        private SalaryService $salaryService
    ) {}

    public function mySalaries(Request $request)
    {
        return response()->json(   
            $this->salaryService->getMySalaries(
                $request->user(),   
                $request->only(['month', 'year'])
            )
        );
    }

    public function adminIndex(Request $request)
    {
        return response()->json(
            $this->salaryService->getAllSalaries(
                $request->only(['month', 'year', 'page', 'per_page'])
            )
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'base_salary' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'note' => 'nullable|string'
        ]);

        return response()->json(
            $this->salaryService->createSalary($data)
        );
    }

    public function managementIndex(Request $request)
    {
        $salaries = $this->salaryService
            ->getDepartmentSalaries(
                $request->user(),
                $request->only(['month', 'year'])
            );

        if (!$salaries) {
            return response()->json([
                'message' => 'Không tìm thấy thông tin nhân viên'
            ], 404);
        }

        return response()->json($salaries);
    }
}