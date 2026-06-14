<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Services\UserService;
use App\Services\DashboardService;

class UserController extends Controller   
{
    public function __construct(
        private UserService $userService,
        private DashboardService $dashboardService
    ) {}    

    public function store(Request $request) 
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'position' => 'required',
            'department' => 'required',
            'password' => 'required|min:6',
            'status' => 'required'
        ]);

        $this->userService->createEmployee($data);

        return response()->json([
            'message' => 'Tạo nhân viên thành công'
        ], 201);
    }

    public function update(Request $request, $id) 
    {
        $user = User::findOrFail($id);

        $this->userService->updateUser($user, $request->all());

        return response()->json([
            'message' => 'User update successfully',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $this->userService->deleteUser($user);

        return response()->json([
            'message' => 'Đã xóa tài khoản thành công'
        ]);
    }

    public function dashboard()
    {
        return response()->json(
            $this->dashboardService->getDashboard()
        );
    }

    public function index(Request $request)
    {
        $users = $this->userService->listUsers($request->all());
    
        if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return response()->json([
                'users' => $users->items(),
                'total' => $users->total(),
            ]);
        }

        return response()->json([
            'users' => $users,
            'total' => is_countable($users) ? count($users) : 0,
        ]);
    }

    public function employeeList(Request $request)
    {
        $employees = $this->userService->listEmployees($request->all());
        return response()->json($employees);
    }
}



