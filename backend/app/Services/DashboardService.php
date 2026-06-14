<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaveRequest;

class DashboardService
{
    public function getDashboard() 
    {
        $totalUsers = User::whereHas('employee')->count();

        $recentUsers = User::with(['employee', 'roleRelation'])
            ->whereHas('employee')
            ->latest()
            ->take(5)
            ->get();

        $pendingLeaves = LeaveRequest::where('status', 'Chờ duyệt')->count();

        return [
            'total' => $totalUsers,
            'users' => $recentUsers,
            'pending_leaves' => $pendingLeaves
        ];
    }
}