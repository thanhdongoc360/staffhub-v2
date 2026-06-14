<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function getProfile($user)
    {
        $user->load('employee');

        return [
            'name' => $user->name,
            'email' => $user->email,
            'employee_code' => $user->employee->employee_code,
            'position' => $user->employee->position,
            'department' => $user->employee->department,
            'phone' => $user->employee->phone,
            'status' => $user->employee->status
        ];
    }

    public function updateProfile($user, array $data)
    {
        $user->update([
            'email' => $data['email']
        ]);

        $user->employee->update([
            'phone' => $data['phone']
        ]);
    }

    public function changePassword($user, array $data)
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            return false;
        }

        $user->update([
            'password' => bcrypt($data['new_password'])
        ]);

        return true;
    }
}