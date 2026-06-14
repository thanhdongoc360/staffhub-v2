<?php

namespace App\Services;

use App\Models\User;   
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

class UserService 
{
    public function createEmployee(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = Role::where('name', 'employee')->first();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => $role->id
            ]);

            Employee::create([
                'user_id' => $user->id,
                'employee_code' => 'EMP' . time(),
                'position' => $data['position'],
                'department' => $data['department'],
                'status' => $data['status']
            ]);

            return $user;
        });
    }

    public function updateUser($user, array $data)
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email']
        ]);

        if ($user->employee) {
            $user->employee->update([
                'position' => $data['position'],
                'department' => $data['department']
            ]);
        }

        return $user;
    }

    public function deleteUser($user)
    {
        $user->employee()?->delete();
        $user->notifications()?->delete();
        $user->delete();
    }

    public function listUsers(array $params = [])
    {
        // join employees to enable filtering/sorting on employee fields
        $query = User::query()->with('employee', 'roleRelation')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->select('users.*');

        // search across user and employee fields
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('employees.employee_code', 'like', "%{$search}%")
                  ->orWhere('employees.position', 'like', "%{$search}%")
                  ->orWhere('employees.department', 'like', "%{$search}%");
            });
        }

        // filter by role
        if (!empty($params['role'])) {
            $role = $params['role'];
            $query->whereHas('roleRelation', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // filter by employee status (accept english or common vietnamese variants)
        if (array_key_exists('status', $params) && $params['status'] !== null && $params['status'] !== '') {
            $statusRaw = $params['status'];
            $statusMap = [
                'Đang làm' => 'active',
                'Đang làm việc' => 'active',
                'Đang làm việc ' => 'active',
                'Nghỉ việc' => 'inactive',
                'inactive' => 'inactive',
                'active' => 'active'
            ];

            $status = $statusMap[$statusRaw] ?? $statusRaw;
            $query->where('employees.status', $status);
        }

        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 10;

        // sorting
        $sortBy = $params['sort_by'] ?? null;
        $sortOrder = strtolower($params['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'id' => 'users.id',
            'name' => 'users.name',
            'position' => 'employees.position',
            'department' => 'employees.department',
            'created_at' => 'users.created_at'
        ];

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            $query = $query->orderBy($allowedSorts[$sortBy], $sortOrder);
        } else {
            $query = $query->orderBy('users.created_at', 'desc');
        }

        if ($perPage && $perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function listEmployees(array $params = [])
    {
        $query = Employee::with('user');

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if (array_key_exists('status', $params) && $params['status'] !== null) {
            $query->where('status', $params['status']);
        }

        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 10;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
