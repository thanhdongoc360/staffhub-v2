<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function getNotifications($user, array $filters = [])
    {
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : null;
        $filterType = $filters['filter_type'] ?? null;

        // ADMIN
        if ($user->role === 'admin') {

            $query = Notification::with('user')
                ->orderBy('created_at', 'desc');

            if ($filterType === 'unread') {
                $query->where('is_read', false);
            }

            $notifications = $perPage && $perPage > 0
                ? $query->paginate($perPage)
                : $query->get();

            return [
                'success' => true,
                'data' => $notifications
            ];
        }

        // MANAGEMENT
        if ($user->role === 'management') {

            if (!$user->employee) {
                return [
                    'success' => false,
                    'data' => []
                ];
            }

            $department = $user->employee->department;

            $query = Notification::with('user')
                ->whereHas('user.employee', function ($q) use ($department) {
                    $q->where('department', $department);
                })
                ->orderBy('created_at', 'desc');

            if ($filterType === 'unread') {
                $query->where('is_read', false);
            }

            $notifications = $perPage && $perPage > 0
                ? $query->paginate($perPage)
                : $query->get();

            return [
                'success' => true,
                'data' => $notifications
            ];
        }

        // EMPLOYEE
        $query = Notification::where(
                'user_id',
                $user->id
            )
            ->orderBy('created_at', 'desc');

        if ($filterType === 'unread') {
            $query->where('is_read', false);
        }

        $notifications = $perPage && $perPage > 0
            ? $query->paginate($perPage)
            : $query->get();

        return [
            'success' => true,
            'data' => $notifications
        ];
    }

    public function markAsRead($user, int $id)
    {
        $notification = Notification::with(
                'user.employee'
            )
            ->findOrFail($id);

        // ADMIN
        if ($user->role === 'admin') {

            $notification->update([
                'is_read' => true
            ]);

            return [
                'success' => true
            ];
        }

        // MANAGEMENT
        if ($user->role === 'management') {

            if (!$user->employee) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized',
                    'code' => 403
                ];
            }

            $department = $user->employee->department;

            if (
                $notification
                    ->user
                    ->employee
                    ->department !== $department
            ) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized',
                    'code' => 403
                ];
            }

            $notification->update([
                'is_read' => true
            ]);

            return [
                'success' => true
            ];
        }

        // EMPLOYEE
        if ($notification->user_id !== $user->id) {

            return [
                'success' => false,
                'message' => 'Unauthorized',
                'code' => 403
            ];
        }

        $notification->update([
            'is_read' => true
        ]);

        return [
            'success' => true
        ];
    }

    public function markAllAsRead($user)
    {
        // ADMIN
        if ($user->role === 'admin') {

            Notification::where(
                    'is_read',
                    false
                )
                ->update([
                    'is_read' => true
                ]);

            return;
        }

        // MANAGEMENT
        if ($user->role === 'management') {

            if (!$user->employee) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized',
                    'code' => 403
                ];
            }

            $department = $user->employee->department;

            Notification::whereHas(
                'user.employee',
                function ($q) use ($department) {
                    $q->where(
                        'department',
                        $department
                    );
                }
            )
            ->where('is_read', false)
            ->update([
                'is_read' => true
            ]);

            return [
                'success' => true
            ];
        }

        // EMPLOYEE
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true
            ]);

        return [
            'success' => true
        ];
    }
}