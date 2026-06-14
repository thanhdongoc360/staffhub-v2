<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\ManagementEmployeeService;

class ManagementEmployeeController extends Controller
{
    public function __construct(
        private ManagementEmployeeService $employeeService
    ) {}

    public function index(Request $request)
    {
        return response()->json(

            $this->employeeService
                ->getEmployees(
                    $request->user(),

                    $request->only([
                        'search',
                        'status',
                        'sort_by',
                        'sort_order'
                    ])
                )
        );
    }

    public function show(
        Request $request,
        int $id
    ) {
        return response()->json(

            $this->employeeService
                ->showEmployee(
                    $request->user(),
                    $id
                )
        );
    }

    public function dashboard(Request $request)
    {
        $result = $this->employeeService
            ->getDashboard(
                $request->user()
            );

        if (!$result['success']) {

            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json(
            $result['data']
        );
    }
}