<?php

namespace App\Services;

use App\Models\Salary;
use Illuminate\Support\Facades\DB;

class AccountantSalaryService
{
    public function getSalaries(array $filters = [])
    {
        $query = DB::table('salaries')
            ->join(
                'employees',
                'salaries.employee_id',
                '=',
                'employees.id'
            )
            ->join(
                'users',
                'employees.user_id',
                '=',
                'users.id'
            )
            ->select(
                'salaries.*',
                'users.name as user_name'
            );

        if (!empty($filters['month'])) {
            $query->where(
                'salaries.month',
                $filters['month']
            );
        }

        if (!empty($filters['year'])) {
            $query->where(
                'salaries.year',
                $filters['year']
            );
        }

        if (!empty($filters['status'])) {
            $query->where(
                'salaries.status',
                $filters['status']
            );
        }

        if (!empty($filters['search'])) {

            $query->where(
                'users.name',
                'like',
                '%' . $filters['search'] . '%'
            );
        }

        return $query
            ->orderBy('salaries.id', 'desc')
            ->paginate(10);
    }

    public function calculate(
        int $month,
        int $year
    )
    {
        Salary::where('month', $month)
            ->where('year', $year)
            ->where('status', 'draft')
            ->get()
            ->each(function ($salary) {

                $salary->total =
                    $salary->base_salary
                    + $salary->bonus
                    - $salary->tax;

                $salary->status =
                    'calculated';

                $salary->save();
            });
    }

    public function approve(
        int $month,
        int $year
    )
    {
        Salary::where('month', $month)
            ->where('year', $year)
            ->where('status', 'calculated')
            ->update([
                'status' => 'approved'
            ]);
    }

    public function publish(
        int $month,
        int $year
    )
    {
        Salary::where('month', $month)
            ->where('year', $year)
            ->where('status', 'approved')
            ->update([
                'status' => 'published'
            ]);
    }

    public function show(int $id)
    {
        return Salary::with(
            'employee.user'
        )->findOrFail($id);
    }

    public function updateSalary(
        int $id,
        array $data
    )
    {
        $salary = Salary::findOrFail($id);

        if ($salary->status === 'published') {

            return [
                'success' => false,
                'message' =>
                    'Cannot edit published salary',
                'code' => 403
            ];
        }

        $salary->base_salary =
            $data['base_salary'];

        $salary->bonus =
            $data['bonus'] ?? 0;

        $salary->tax =
            $data['tax'] ?? 0;

        $salary->note =
            $data['note'];

        $salary->total =
            $salary->base_salary
            + $salary->bonus
            - $salary->tax;

        $salary->save();

        return [
            'success' => true,
            'data' => $salary
        ];
    }

    public function calculateOne(int $id)
    {
        $salary = Salary::findOrFail($id);

        if ($salary->status !== 'draft') {

            return [
                'success' => false,
                'message' =>
                    'Chỉ tính khi là draft',
                'code' => 400
            ];
        }

        $salary->total =
            $salary->base_salary
            + $salary->bonus
            - $salary->tax;

        $salary->status =
            'calculated';

        $salary->save();

        return [
            'success' => true,
            'data' => $salary
        ];
    }

    public function approveOne(int $id)
    {
        $salary = Salary::findOrFail($id);

        if ($salary->status !== 'calculated') {

            return [
                'success' => false,
                'message' => 'Phải tính trước',
                'code' => 400
            ];
        }

        $salary->status = 'approved';

        $salary->save();

        return [
            'success' => true,
            'data' => $salary
        ];
    }

    public function publishOne(int $id)
    {
        $salary = Salary::findOrFail($id);

        if ($salary->status !== 'approved') {

            return [
                'success' => false,
                'message' => 'Phải duyệt trước',
                'code' => 400
            ];
        }

        $salary->status = 'published';

        $salary->save();

        return [
            'success' => true,
            'data' => $salary
        ];
    }

    public function createSalaryTable(
        int $month,
        int $year
    )
    {
        $prevMonth =
            $month == 1 ? 12 : $month - 1;

        $prevYear =
            $month == 1 ? $year - 1 : $year;

        $prevSalaries = Salary::where(
                'month',
                $prevMonth
            )
            ->where('year', $prevYear)
            ->get()
            ->keyBy('employee_id');

        $existing = Salary::where(
                'month',
                $month
            )
            ->where('year', $year)
            ->pluck('employee_id')
            ->toArray();

        $employees = DB::table('employees')
            ->whereNotIn('id', $existing)
            ->get();

        if ($employees->isEmpty()) {

            return [
                'success' => false,
                'message' =>
                    'Bảng lương tháng này đã được tạo rồi',
                'code' => 400
            ];
        }

        $data = [];

        foreach ($employees as $emp) {

            $prev =
                $prevSalaries->get($emp->id);

            $data[] = [

                'employee_id' => $emp->id,

                'month' => $month,

                'year' => $year,

                'base_salary' =>
                    $prev->base_salary ?? 0,

                'bonus' =>
                    $prev->bonus ?? 0,

                'tax' =>
                    $prev->tax ?? 0,

                'total' =>
                    ($prev->base_salary ?? 0)
                    + ($prev->bonus ?? 0)
                    - ($prev->tax ?? 0),

                'note' =>
                    $prev->note ?? '',

                'status' => 'draft',

                'created_at' => now(),

                'updated_at' => now()
            ];
        }

        Salary::insert($data);

        return [
            'success' => true
        ];
    }
}