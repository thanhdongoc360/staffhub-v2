<template>
    <div>
        <div class="container-fluid mt-3">
            <a-button @click="showSidebar = true" class="d-lg-none mb-3">
                <i class="fa-solid fa-bars"></i>
            </a-button>

            <a-drawer :visible="showSidebar" placement="left" width="260" @close="showSidebar = false"
                class="d-lg-none">
                <SidebarAccountant />
            </a-drawer>

            <div class="row">
                <div class="d-none d-lg-block col-lg-3">
                    <SidebarAccountant />
                </div>

                <div class="col-12 col-lg-9">
                    <div
                        class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mt-3 gap-2">
                        <h1 class="mb-0">Dashboard kế toán</h1>
                    </div>

                    <div class="mt-3">
                        <div class="row g-2 align-items-stretch align-items-md-center">

                            <div class="col-12 col-sm-6 col-md-auto">
                                <select v-model="month" class="form-select form-select-sm">
                                    <option v-for="m in 12" :key="m" :value="m">
                                        Tháng {{ m }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-auto">
                                <input v-model.number="year" type="number" class="form-control form-control-sm"
                                    placeholder="Năm" style="width: 120px" />
                            </div>

                            <div class="col-12 col-sm-auto">
                                <button class="btn btn-primary btn-sm w-100 w-sm-auto" @click="loadDashboard">
                                    Tìm kiếm
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="row mt-4 g-3">
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card h-100 dashboard-card">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="dashboard-icon bg-primary-subtle text-primary">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                    </div>

                                    <div>
                                        <p class="mb-1 text-muted small">Tổng quỹ lương</p>
                                        <h5 class="mb-0">{{ formatMoney(data.total_payroll) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card h-100 dashboard-card">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="dashboard-icon bg-success-subtle text-success">
                                        <i class="fa-solid fa-users"></i>
                                    </div>

                                    <div>
                                        <p class="mb-1 text-muted small">Tổng nhân viên</p>
                                        <h5 class="mb-0">{{ data.total_employees }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card h-100 dashboard-card">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="dashboard-icon bg-secondary-subtle text-secondary">
                                        <i class="fa-solid fa-pen"></i>
                                    </div>

                                    <div>
                                        <p class="mb-1 text-muted small">Bản nháp</p>
                                        <h5 class="mb-0">{{ data.status?.draft || 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card h-100 dashboard-card">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="dashboard-icon bg-warning-subtle text-warning">
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </div>

                                    <div>
                                        <p class="mb-1 text-muted small">Đã công bố</p>
                                        <h5 class="mb-0">{{ data.status?.published || 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div
                                    class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2 mb-3">
                                    <h5 class="mb-0">Danh sách lương gần đây</h5>

                                    <a-button type="primary" @click="salaryManagement">
                                        Danh sách bảng lương
                                    </a-button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Mã NV</th>
                                                <th>Tên nhân viên</th>
                                                <th>Phòng ban</th>
                                                <th>Lương cơ bản</th>
                                                <th>Thưởng</th>
                                                <th>Thuế</th>
                                                <th>Tổng lương</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr v-for="salary in data.latest_salaries" :key="salary.id">
                                                <td>{{ salary.employee_code }}</td>
                                                <td>{{ salary.employee_name }}</td>
                                                <td>{{ salary.department }}</td>
                                                <td>{{ formatMoney(salary.base_salary) }}</td>
                                                <td>{{ formatMoney(salary.bonus) }}</td>
                                                <td>{{ formatMoney(salary.tax) }}</td>
                                                <td>{{ formatMoney(salary.total) }}</td>
                                                <td>
                                                    <span class="badge" :class="{
                                                        'bg-secondary': salary.status === 'draft',
                                                        'bg-success': salary.status === 'published',
                                                    }">
                                                        {{ formatStatus(salary.status) }}
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr v-if="!data.latest_salaries || data.latest_salaries.length === 0">
                                                <td colspan="8" class="text-center text-muted">
                                                    Không có dữ liệu lương
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarAccountant from '../../components/SidebarAccountant.vue'
import http from '../../services/http'

const showSidebar = ref(false)

const now = new Date()

const month = ref(now.getMonth() + 1)
const year = ref(now.getFullYear())

const router = useRouter()

const salaryManagement = () => {
    router.push('/accountant/salaries')
}

const data = ref({
    total_payroll: 0,
    total_employees: 0,
    not_calculated: 0,
    warnings: {},
    status: {},
    latest_salaries: []
})

const loadDashboard = async () => {
    const res = await http.get('/accountant/dashboard', {
        params: {
            month: month.value,
            year: year.value
        }
    })

    data.value = res.data
}

const formatMoney = (value) => {
    return Number(value || 0).toLocaleString('vi-VN') + ' VNĐ'
}

const formatStatus = (status) => {
    const map = {
        draft: 'Bản nháp',
        published: 'Đã công bố'
    }

    return map[status] || status
}

onMounted(() => {
    loadDashboard()
})


</script>

<style scoped>
.dashboard-card {
    background-color: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    border: none;
}

.dashboard-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
</style>