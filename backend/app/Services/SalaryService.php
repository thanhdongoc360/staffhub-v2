<?php

namespace App\Services;

use App\Models\Salary;

class SalaryService
{
    public function getMySalaries($user, array $filters = [])
    {
        $query = $user->employee
            ->salaries()
            ->where('status', 'published')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        return $this->applyFilters($query, $filters)->get();
    }

    public function getAllSalaries(array $filters = [])
    {
        $query = Salary::with('employee.user')
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];

                $q->whereHas('employee', function ($employeeQuery) use ($search) {
                    $employeeQuery
                        ->where('employee_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        $query = $this->applyFilters($query, $filters);

        $perPage = isset($filters['per_page'])
            ? (int) $filters['per_page']
            : 10;

        return $query->paginate($perPage);
    }

    public function createSalary(array $data)
    {
        $salary = Salary::create([
            'employee_id' => $data['employee_id'],
            'month' => $data['month'],
            'year' => $data['year'],
            'base_salary' => $data['base_salary'],
            'bonus' => $data['bonus'] ?? 0,
            'total' => $data['base_salary'] + ($data['bonus'] ?? 0),
            'note' => $data['note'] ?? ''
        ]);

        return $salary->load('employee.user');
    }

    public function getDepartmentSalaries($user, array $filters = [])
    {
        if (!$user->employee) {
            return null;
        }

        $department = $user->employee->department;

        $query = Salary::with('employee.user')
            ->whereHas('employee', function ($q) use ($department) {
                $q->where('department', $department);
            })
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];

                $q->whereHas('employee', function ($employeeQuery) use ($search) {
                    $employeeQuery
                        ->where('employee_code', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        return $this->applyFilters($query, $filters)->get();
    }

    private function applyFilters($query, array $filters)
    {
        // Lọc theo tháng
        if (!empty($filters['month'])) {
            $query->where('month', $filters['month']);
        }

        // Lọc theo năm
        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        return $query;
    }
}
