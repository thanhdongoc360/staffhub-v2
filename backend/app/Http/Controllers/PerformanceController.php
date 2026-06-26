<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PerformanceService;

class PerformanceController extends Controller
{
    public function __construct(  
        private PerformanceService $performanceService
    ) {}
        
    public function index(Request $request)   
    {
        $result = $this->performanceService
            ->getEmployeeReviews(
                auth()->user(),
                $request->only([
                    'month',   
                    'year',
                    'search',
                    'page',
                    'per_page'
                ])
            );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json($result['data']);
    }

    public function show(
        Request $request,
        int $employeeId
    ) {
        $result = $this->performanceService
            ->getReviewDetail(
                auth()->user(),
                $employeeId,
                $request->query('month'),
                $request->query('year')
            );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json($result['data']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer',
            'year' => 'required|integer',

            'kpi_score' => 'nullable|integer|min:0|max:100',
            'discipline_score' => 'nullable|integer|min:0|max:100',
            'collaboration_score' => 'nullable|integer|min:0|max:100',
            'growth_score' => 'nullable|integer|min:0|max:100',
        ]);

        $result = $this->performanceService
            ->saveReview(
                auth()->user(),
                array_merge($data, $request->only([
                    'kpi_comment',
                    'discipline_comment',
                    'collaboration_comment',
                    'reviewer_comment',
                ]))
            );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json($result['data']);
    }

    // public function confirm(int $id)
    // {
    //     $result = $this->performanceService
    //         ->confirmReview(auth()->user(), $id);

    //     if (!$result['success']) {
    //         return response()->json([
    //             'message' => $result['message']
    //         ], $result['code']);
    //     }

    //     return response()->json($result['data']);
    // }

    public function history(int $employeeId)
    {
        return response()->json(
            $this->performanceService
                ->getHistory(
                    auth()->user(),
                    $employeeId
                )
        );
    }
}