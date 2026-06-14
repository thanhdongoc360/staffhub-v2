<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class AdminProfileService
{
    public function getProfile($user)
    {
        $user->load('employee');

        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => optional($user->employee)->phone
        ];
    }

    public function updateProfile($user, array $data)
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email']
        ]);

        if ($user->employee) {

            $user->employee->update([
                'phone' => $data['phone']
            ]);
        }
    }

    public function changePassword($user, array $data)
    {
        if (!Hash::check(
            $data['current_password'],
            $user->password
        )) {
            return [
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng',
                'code' => 422
            ];
        }

        $user->update([
            'password' => bcrypt($data['new_password'])
        ]);

        return [
            'success' => true
        ];
    }
}