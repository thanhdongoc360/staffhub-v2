<template>
    <div>

        <div class="container-fluid mt-3">
            <a-button @click="showSidebar = true" class="d-lg-none mb-3">
                <i class="fa-solid fa-bars"></i>
            </a-button>

            <a-drawer
                :visible="showSidebar"
                placement="left"
                width="260"
                @close="showSidebar = false"
                class="d-lg-none"
            >
                <SidebarManagement />
            </a-drawer>

            <div class="row">

                <div class="d-none d-lg-block col-lg-3">
                    <SidebarManagement />
                </div>

                <div class="col-12 col-lg-9">

                    <div
                        class="d-flex flex-column flex-lg-row
                        justify-content-between
                        align-items-start
                        align-items-lg-center
                        mt-3 gap-2"
                    >
                        <h1 class="mb-0">
                            Tạo lịch làm việc
                        </h1>
                    </div>

                    <div class="row mt-4 g-3">

                        <!-- Phân ca nhanh -->
                        <div class="col-12 col-xl-8">

                            <div class="card p-3 h-100">

                                <h2 class="h5 mb-3">
                                    Phân ca nhanh
                                </h2>

                                <div class="row g-3">

                                    <!-- Shift -->
                                    <div class="col-12 col-md-6">

                                        <label class="form-label fw-semibold mb-1">
                                            Chọn ca
                                        </label>

                                        <select
                                            v-model="shiftId"
                                            class="form-select"
                                        >
                                            <option
                                                :value="null"
                                                disabled
                                            >
                                                Chọn ca làm việc
                                            </option>

                                            <option
                                                v-for="s in shifts"
                                                :key="s.id"
                                                :value="s.id"
                                            >
                                                {{ s.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Date -->
                                    <div class="col-12 col-md-6">

                                        <label class="form-label fw-semibold mb-1">
                                            Ngày làm việc
                                        </label>

                                        <input
                                            type="date"
                                            v-model="date"
                                            class="form-control"
                                        />
                                    </div>

                                    <!-- Employees -->
                                    <div class="col-12">

                                        <label class="form-label fw-semibold mb-2">
                                            Nhân viên
                                        </label>

                                        <div class="employee-list">

                                            <div
                                                v-for="e in employees"
                                                :key="e.id"
                                                class="form-check employee-item"
                                            >
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    :value="e.id"
                                                    v-model="selectedEmployees"
                                                    :id="`employee-${e.id}`"
                                                />

                                                <label
                                                    class="form-check-label"
                                                    :for="`employee-${e.id}`"
                                                >
                                                    {{ e.user.name }}
                                                </label>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- Button -->
                                    <div class="col-12">

                                        <button
                                            class="btn btn-primary d-block d-sm-inline-block"
                                            @click="assign"
                                        >
                                            Phân ca
                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Tạo lịch tuần -->
                        <div class="col-12 col-xl-4">

                            <div class="card p-3 h-100">

                                <h2 class="h5 mb-3">
                                    Tạo lịch tuần
                                </h2>

                                <label class="form-label fw-semibold mb-1">
                                    Ngày bắt đầu tuần
                                </label>

                                <input
                                    type="date"
                                    v-model="startDate"
                                    class="form-control"
                                />

                                <button
                                    class="btn btn-success d-block d-sm-inline-block mt-3"
                                    @click="createWeek"
                                >
                                    Tạo lịch tuần
                                </button>

                            </div>

                        </div>

                    </div>

                    <!-- Table -->
                    <div class="table-responsive mt-4">

                        <table class="table align-middle">

                            <thead>
                                <tr>
                                    <th>Tên nhân viên</th>

                                    <th class="d-none d-md-table-cell">
                                        Ngày
                                    </th>

                                    <th>Ca làm việc</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr
                                    v-for="item in list"
                                    :key="item.id"
                                >
                                    <td>
                                        {{ item.employee?.user?.name }}
                                    </td>

                                    <td class="d-none d-md-table-cell">
                                        {{ item.work_date }}
                                    </td>

                                    <td>
                                        {{ item.shift?.name }}
                                    </td>
                                </tr>

                                <tr v-if="list.length === 0">
                                    <td
                                        colspan="3"
                                        class="text-center text-muted"
                                    >
                                        Chưa có dữ liệu lịch làm việc
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { message } from 'ant-design-vue'

import http from '../../services/http'
import SidebarManagement from '../../components/SidebarManagement.vue'

const showSidebar = ref(false)

const shifts = ref([])
const employees = ref([])

const selectedEmployees = ref([])

const shiftId = ref(null)
const date = ref(null)

const list = ref([])

const startDate = ref(null)
const scheduleId = ref(null)

const fetchData = async () => {
    try {
        // shifts
        shifts.value =
            (await http.get('/management/shifts')).data

        // employees
        employees.value =
            (await http.get('/management/employees'))
                .data.employees.data

        // schedule list
        const res =
            await http.get('/management/schedule')

        list.value = res.data

        // current schedule
        const currentScheduleResponse =
            await http.get('/management/schedule/current')

        const currentSchedule =
            currentScheduleResponse.data?.schedule
            ?? currentScheduleResponse.data

        if (currentSchedule?.id) {

            scheduleId.value =
                currentSchedule.id

            startDate.value =
                currentSchedule.start_date
                || startDate.value

        } else {

            scheduleId.value = null

        }

    } catch (error) {

        message.error(
            'Không thể tải dữ liệu'
        )

    }
}

const assign = async () => {

    // validate schedule
    if (!scheduleId.value) {

        message.warning(
            'Vui lòng tạo lịch tuần trước khi phân ca'
        )

        return
    }

    // validate form
    if (
        !shiftId.value
        || !date.value
        || selectedEmployees.value.length === 0
    ) {

        message.warning(
            'Vui lòng chọn ca, ngày làm việc và ít nhất một nhân viên'
        )

        return
    }

    try {

        await http.post(
            '/management/schedule/assign',
            {
                schedule_id: scheduleId.value,

                shift_id: shiftId.value,

                employee_ids: selectedEmployees.value,

                dates: [date.value]
            }
        )

        await fetchData()

        message.success(
            'Phân ca thành công'
        )

    } catch (error) {

        message.error(
            error?.response?.data?.message
            || 'Phân ca thất bại'
        )

    }
}

const createWeek = async () => {

    // validate start date
    if (!startDate.value) {

        message.warning(
            'Vui lòng chọn ngày bắt đầu tuần'
        )

        return
    }

    try {

        const res = await http.post(
            '/management/schedule/create-week',
            {
                start_date: startDate.value
            }
        )

        const schedule =
            res.data.schedule ?? res.data

        const created =
            res.data.created ?? true

        scheduleId.value =
            schedule.id

        startDate.value =
            schedule.start_date || startDate.value

        const start =
            schedule.start_date || startDate.value

        const end =
            schedule.end_date || startDate.value

        await fetchData()

        if (created) {

            message.success(
                `Đã tạo lịch tuần mới từ ${start} đến ${end}`
            )

        } else {

            message.info(
                `Lịch tuần từ ${start} đến ${end} đã tồn tại`
            )

        }

    } catch (error) {

        message.error(
            'Tạo lịch tuần thất bại'
        )

    }
}

onMounted(fetchData)
</script>

<style scoped>
.card {
    background-color: #f8f9fa;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border: none;
}

.employee-list {
    max-height: 260px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 12px;
    background-color: #fff;
}

.employee-item + .employee-item {
    margin-top: 8px;
}

.table td,
.table th {
    vertical-align: middle;
}
</style>