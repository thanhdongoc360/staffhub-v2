<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AccountantDashboardService
{
    public function getDashboard(
        int $month,
        int $year
    )
    {
        $query = DB::table('salaries')
            ->where('month', $month)
            ->where('year', $year);

        return [
            'total_payroll' =>
                (clone $query)->sum('total'),

            'total_employees' =>
                (clone $query)->count(),

            'calculated' =>
                (clone $query)
                    ->where('status', 'calculated')
                    ->count(),

            'not_calculated' =>
                (clone $query)
                    ->where('status', 'draft')
                    ->count(),

            'warnings' => [

                'missing_base_salary' =>
                    (clone $query)
                        ->whereNull('base_salary')
                        ->count(),

                'negative_salary' =>
                    (clone $query)
                        ->where('total', '<', 0)
                        ->count(),
            ],

            'status' => [

                'draft' =>
                    (clone $query)
                        ->where('status', 'draft')
                        ->count(),

                'calculated' =>
                    (clone $query)
                        ->where('status', 'calculated')
                        ->count(),

                'approved' =>
                    (clone $query)
                        ->where('status', 'approved')
                        ->count(),

                'published' =>
                    (clone $query)
                        ->where('status', 'published')
                        ->count(),
            ]
        ];
    }
}