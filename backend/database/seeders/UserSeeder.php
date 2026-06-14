<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 20 Employee users
        $employees = [
            ['name' => 'Nguyễn Văn A', 'email' => 'nguyen.van.a@staffhub.com', 'position' => 'Lập trình viên', 'department' => 'IT', 'status' => 'active'],
            ['name' => 'Trần Thị B', 'email' => 'tran.thi.b@staffhub.com', 'position' => 'Designer', 'department' => 'Design', 'status' => 'active'],
            ['name' => 'Phạm Văn C', 'email' => 'pham.van.c@staffhub.com', 'position' => 'Project Manager', 'department' => 'PM', 'status' => 'active'],
            ['name' => 'Hoàng Thị D', 'email' => 'hoang.thi.d@staffhub.com', 'position' => 'HR Specialist', 'department' => 'HR', 'status' => 'active'],
            ['name' => 'Võ Văn E', 'email' => 'vo.van.e@staffhub.com', 'position' => 'Lập trình viên', 'department' => 'IT', 'status' => 'active'],
            ['name' => 'Dương Thị F', 'email' => 'duong.thi.f@staffhub.com', 'position' => 'Marketing Executive', 'department' => 'Marketing', 'status' => 'active'],
            ['name' => 'Bùi Văn G', 'email' => 'bui.van.g@staffhub.com', 'position' => 'QA Engineer', 'department' => 'QA', 'status' => 'active'],
            ['name' => 'Lê Thị H', 'email' => 'le.thi.h@staffhub.com', 'position' => 'Business Analyst', 'department' => 'BA', 'status' => 'active'],
            ['name' => 'Đặng Văn I', 'email' => 'dang.van.i@staffhub.com', 'position' => 'DevOps Engineer', 'department' => 'IT', 'status' => 'active'],
            ['name' => 'Giang Thị J', 'email' => 'giang.thi.j@staffhub.com', 'position' => 'Content Writer', 'department' => 'Marketing', 'status' => 'active'],
            ['name' => 'Hà Văn K', 'email' => 'ha.van.k@staffhub.com', 'position' => 'Senior Developer', 'department' => 'IT', 'status' => 'active'],
            ['name' => 'Khánh Thị L', 'email' => 'khanh.thi.l@staffhub.com', 'position' => 'UI/UX Designer', 'department' => 'Design', 'status' => 'active'],
            ['name' => 'Minh Văn M', 'email' => 'minh.van.m@staffhub.com', 'position' => 'Technical Lead', 'department' => 'IT', 'status' => 'active'],
            ['name' => 'Ngân Thị N', 'email' => 'ngan.thi.n@staffhub.com', 'position' => 'Sales Executive', 'department' => 'Sales', 'status' => 'active'],
            ['name' => 'Phúc Văn O', 'email' => 'phuc.van.o@staffhub.com', 'position' => 'Support Specialist', 'department' => 'Support', 'status' => 'active'],
            ['name' => 'Quỳnh Thị P', 'email' => 'quynh.thi.p@staffhub.com', 'position' => 'Graphic Designer', 'department' => 'Design', 'status' => 'active'],
            ['name' => 'Tùng Văn Q', 'email' => 'tung.van.q@staffhub.com', 'position' => 'Database Admin', 'department' => 'IT', 'status' => 'inactive'],
            ['name' => 'Uyên Thị R', 'email' => 'uyen.thi.r@staffhub.com', 'position' => 'Recruiter', 'department' => 'HR', 'status' => 'active'],
            ['name' => 'Vinh Văn S', 'email' => 'vinh.van.s@staffhub.com', 'position' => 'Finance Manager', 'department' => 'Finance', 'status' => 'active'],
            ['name' => 'Xuân Thị T', 'email' => 'xuan.thi.t@staffhub.com', 'position' => 'Admin Assistant', 'department' => 'Admin', 'status' => 'active'],
        ];

        foreach ($employees as $index => $emp) {
            $userId = DB::table('users')->insertGetId([
                'name' => $emp['name'],
                'email' => $emp['email'],
                'password' => Hash::make('123456'),
                'role' => 'employee',
                'role_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('employees')->insert([
                'user_id' => $userId,
                'employee_code' => 'EMP' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'position' => $emp['position'],
                'department' => $emp['department'],
                'phone' => '0' . rand(900000000, 999999999),
                'status' => $emp['status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
