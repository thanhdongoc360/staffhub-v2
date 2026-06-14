<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AccountantSalaryService;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalaryExport;

class AccountantSalaryController extends Controller
{
    public function __construct(
        private AccountantSalaryService $salaryService
    ) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->salaryService
                ->getSalaries(
                    $request->only([
                        'month',
                        'year',
                        'status',
                        'search'
                    ])
                )
        );
    }

    public function calculate(Request $request)
    {
        $this->salaryService->calculate(
            $request->month,
            $request->year
        );

        return response()->json([
            'message' => 'Calculated'
        ]);
    }

    public function approve(Request $request)
    {
        $this->salaryService->approve(
            $request->month,
            $request->year
        );

        return response()->json([
            'message' => 'Approved'
        ]);
    }

    public function publish(Request $request)
    {
        $this->salaryService->publish(
            $request->month,
            $request->year
        );

        return response()->json([
            'message' => 'Published'
        ]);
    }

    public function show(int $id)
    {
        return response()->json(
            $this->salaryService->show($id)
        );
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000'
        ]);

        $result = $this->salaryService
            ->createSalaryTable(
                $data['month'],
                $data['year']
            );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Created'
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'base_salary' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'note' => 'nullable|string'
        ]);

        $result = $this->salaryService->updateSalary($id, $data);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json($result['data']);
    }

    public function calculateOne(int $id)
    {
        $result = $this->salaryService->calculateOne($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json($result['data']);
    }

    public function approveOne(int $id)
    {
        $result = $this->salaryService->approveOne($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json($result['data']);
    }

    public function publishOne(int $id)
    {
        $result = $this->salaryService->publishOne($id);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json($result['data']);
    }

    public function export(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000'
        ]);

        return Excel::download(
            new SalaryExport(
                $data['month'],
                $data['year']
            ),
            'salary.xlsx'
        );
    }
}