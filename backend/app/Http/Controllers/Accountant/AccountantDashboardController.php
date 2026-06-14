<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AccountantDashboardService;

class AccountantDashboardController extends Controller
{
    public function __construct(
        private AccountantDashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000'
        ]);

        return response()->json(
            $this->dashboardService
                ->getDashboard(
                    $request->month,
                    $request->year
                )
        );
    }
}