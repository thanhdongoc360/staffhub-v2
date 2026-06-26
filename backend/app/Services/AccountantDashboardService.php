<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AccountantDashboardService
{
    public function getDashboard(
        int $month,
        int $year
    ) {
        $query = DB::table('salaries')
            ->where('month', $month)
            ->where('year', $year);

        return [
            'total_payroll' => (clone $query)->sum('total'),

            'total_employees' => (clone $query)->count(),

            'status' => [

                'draft' => (clone $query)
                    ->where('status', 'draft')
                    ->count(),

                'published' => (clone $query)
                    ->where('status', 'published')
                    ->count(),
            ],

            'latest_salaries' => (clone $query)
                ->join('employees', 'salaries.employee_id', '=', 'employees.id')
                ->join('users', 'employees.user_id', '=', 'users.id')
                ->select(
                    'salaries.id',
                    'users.name as employee_name',
                    'employees.employee_code',
                    'employees.position',
                    'employees.department',
                    'salaries.base_salary',
                    'salaries.bonus',
                    'salaries.tax',
                    'salaries.total',
                    'salaries.status',
                    'salaries.month',
                    'salaries.year'
                )
                ->orderByDesc('salaries.id')
                ->limit(5)
                ->get(),
        ];
    }
}
