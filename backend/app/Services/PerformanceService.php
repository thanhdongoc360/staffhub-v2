<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PerformanceReview;

class PerformanceService
{
    public function getEmployeeReviews($user, array $filters = [])
    {
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;
        $search = $filters['search'] ?? null;
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;

        $query = Employee::query();

        if ($user->role === 'management') {

            $employee = $user->employee;

            if (!$employee) {
                return [
                    'success' => false,
                    'message' => 'User chưa liên kết employee',
                    'code' => 400
                ];
            }

            $query->where(
                'department',
                $employee->department
            );
        }

        $paginator = $query
            ->when($search, function ($q) use ($search) {
                $q->where('employee_code', 'like', "%$search%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%$search%");
                    });
            })
            ->whereHas('performanceReviews', function ($q) use ($month, $year) {
                $q->where('period_month', $month)
                    ->where('period_year', $year);
            })
            ->with([
                'user',
                'performanceReviews' => function ($q) use ($month, $year) {
                    $q->where('period_month', $month)
                        ->where('period_year', $year);
                }
            ])
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $paginator->getCollection()->transform(function ($emp) {

                $review = $emp->performanceReviews->first();

                return [
                    'id' => $emp->id,
                    'name' => $emp->user->name ?? null,
                    'code' => $emp->employee_code,
                    'position' => $emp->position,

                    'review_id' => $review->id ?? null,
                    'status' => $review->status ?? 'not_reviewed',
                    'total_score' => $review->total_score ?? null,
                    'rank' => $review->rank ?? null,
                ];
            });

        return [
            'success' => true,
            'data' => $paginator
        ];
    }

    public function getReviewDetail(
        $user,
        int $employeeId,
        $month,
        $year
    ) {
        $query = Employee::with('user')
            ->where('id', $employeeId);

        if ($user->role === 'management') {

            $employee = $user->employee;

            if (!$employee) {
                return [
                    'success' => false,
                    'message' => 'No employee linked',
                    'code' => 400
                ];
            }

            $query->where(
                'department',
                $employee->department
            );
        }

        $employee = $query->firstOrFail();

        $review = PerformanceReview::where(
                'employee_id',
                $employeeId
            )
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();

        if (!$review && $user->role === 'management') {

            $review = PerformanceReview::create([
                'employee_id' => $employeeId,
                'reviewer_id' => $user->id,
                'period_month' => $month,
                'period_year' => $year,
                'status' => 'draft',
            ]);
        }

        return [
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->user->name ?? null,
                    'code' => $employee->employee_code,
                    'position' => $employee->position,
                    'department' => $employee->department,
                ],
                'review' => [
                    'id' => $review->id,
                    'kpi_score' => $review->kpi_score ?? 0,
                    'discipline_score' => $review->discipline_score ?? 0,
                    'collaboration_score' => $review->collaboration_score ?? 0,
                    'growth_score' => $review->growth_score ?? 0,
                    'reviewer_comment' => $review->reviewer_comment ?? '',
                    'total_score' => $review->total_score,
                    'rank' => $review->rank,
                    'status' => $review->status,
                ]
            ]
        ];
    }

    public function saveReview($user, array $data)
    {
        if ($user->role === 'admin') {
            return [
                'success' => false,
                'message' => 'Admin chỉ được xem',
                'code' => 403
            ];
        }

        $userEmployee = $user->employee;

        Employee::where('id', $data['employee_id'])
            ->where('department', $userEmployee->department)
            ->firstOrFail();

        $review = PerformanceReview::updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'period_month' => $data['month'],
                'period_year' => $data['year'],
            ],
            [
                'reviewer_id' => $user->id,

                'kpi_score' => $data['kpi_score'],
                'discipline_score' => $data['discipline_score'],
                'collaboration_score' => $data['collaboration_score'],
                'growth_score' => $data['growth_score'],

                'kpi_comment' => $data['kpi_comment'] ?? null,
                'discipline_comment' => $data['discipline_comment'] ?? null,
                'collaboration_comment' => $data['collaboration_comment'] ?? null,
                'reviewer_comment' => $data['reviewer_comment'] ?? null,

                'status' => $data['status'] ?? 'draft',
            ]
        );

        return [
            'success' => true,
            'data' => $review->fresh()
        ];
    }

    public function confirmReview($user, int $id)
    {
        if ($user->role_id !== 3) {
            return [
                'success' => false,
                'message' => 'Forbidden',
                'code' => 403
            ];
        }

        $review = PerformanceReview::findOrFail($id);

        if ($review->status !== 'submitted') {
            return [
                'success' => false,
                'message' => 'Chỉ confirm khi đã submitted',
                'code' => 400
            ];
        }

        $review->update([
            'status' => 'confirmed'
        ]);

        return [
            'success' => true,
            'data' => $review
        ];
    }

    public function getHistory($user, int $employeeId)
    {
        $userEmployee = $user->employee;

        Employee::where('id', $employeeId)
            ->where('department', $userEmployee->department)
            ->firstOrFail();

        return PerformanceReview::where(
                'employee_id',
                $employeeId
            )
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->take(6)
            ->get();
    }
}