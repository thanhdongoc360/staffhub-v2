<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class ManagementProfileService
{
    public function getProfile($user)
    {
        $user->load('employee');

        return [
            'name' => $user->name,
            'email' => $user->email,

            'employee_code' =>
                $user->employee->employee_code ?? null,

            'position' =>
                $user->employee->position ?? null,

            'department' =>
                $user->employee->department ?? null,

            'phone' =>
                $user->employee->phone ?? null,

            'status' =>
                $user->employee->status ?? null,
        ];
    }

    public function updateProfile(
        $user,
        array $data
    )
    {
        $user->update([
            'email' => $data['email']
        ]);

        if ($user->employee) {

            $user->employee->update([
                'phone' => $data['phone']
            ]);
        }
    }

    public function changePassword(
        $user,
        string $currentPassword,
        string $newPassword
    )
    {
        if (
            !Hash::check(
                $currentPassword,
                $user->password
            )
        ) {

            return [
                'success' => false,
                'message' =>
                    'Mật khẩu hiện tại không đúng',
                'code' => 400
            ];
        }

        $user->update([
            'password' =>
                Hash::make($newPassword)
        ]);

        return [
            'success' => true
        ];
    }
}