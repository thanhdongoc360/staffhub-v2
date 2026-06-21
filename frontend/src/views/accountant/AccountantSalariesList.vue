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
                        class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center mt-3 gap-2 w-100">
                        <h1 class="mb-0">Danh sách bảng lương</h1>

                        <div class="d-flex flex-column flex-sm-row gap-2 ms-lg-auto">
                            <button class="btn btn-primary d-block d-sm-inline-block" @click="exportExcel">
                                Xuất Excel
                            </button>

                            <button class="btn btn-success d-block d-sm-inline-block" @click="createSalary">
                                Tạo bảng lương tháng
                            </button>
                        </div>
                    </div>

                    <!-- Filter -->
                    <div class="mt-3">
                        <div
                            class="d-flex flex-column flex-sm-row flex-wrap gap-2 align-items-stretch align-items-sm-center">

                            <input v-model="search" placeholder="Tìm kiếm theo tên" class="form-control w-100"
                                style="max-width: 220px;" />

                            <!-- Month -->
                            <select v-model="month" class="form-select w-100" style="max-width: 150px;">
                                <option v-for="m in 12" :key="m" :value="m">
                                    Tháng {{ m }}
                                </option>
                            </select>

                            <!-- Year -->
                            <input v-model.number="year" type="number" class="form-control w-100"
                                style="max-width: 150px;" placeholder="Năm" />

                            <select v-model="status" class="form-select w-100" style="max-width: 220px;">
                                <option value="">Tất cả</option>
                                <option value="draft">Nháp</option>
                                <option value="published">Đã công bố</option>
                            </select>

                            <button class="btn btn-primary" @click="loadSalaries">
                                Tìm kiếm
                            </button>
                        </div>
                    </div>


                    <!-- Action buttons -->
                    <div class="mt-3">
                        <div class="d-flex flex-column flex-sm-row gap-2 flex-wrap">

                            <button class="btn btn-success d-block d-sm-inline-block" @click="publish">
                                Công bố toàn bộ
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nhân viên</th>
                                    <th>Tổng lương</th>
                                    <th class="d-none d-md-table-cell">Tháng/Năm</th>
                                    <th>Trạng thái</th>
                                    <th class="d-none d-lg-table-cell">Hành động</th>
                                    <th>Thực hiện</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="s in salaries" :key="s.id">
                                    <td class="text-nowrap">{{ s.user_name }}</td>
                                    <td>{{ s.total }}</td>
                                    <td class="d-none d-md-table-cell">{{ s.month }} / {{ s.year }}</td>
                                    <td>
                                        <span v-if="s.status === 'draft'" class="badge bg-secondary">
                                            Nháp
                                        </span>

                                        <span v-else class="badge bg-success">
                                            Đã công bố
                                        </span>
                                    </td>

                                    <td class="d-none d-lg-table-cell">
                                        <div class="d-flex flex-column flex-xl-row gap-2">
                                            <button class="btn btn-success btn-sm" @click="publishOne(s.id)"
                                                :disabled="s.status === 'published'">
                                                Công bố
                                            </button>
                                        </div>
                                    </td>

                                    <td>
                                        <a-space>
                                            <!-- View -->
                                            <i class="fa-solid fa-eye text-info" style="cursor: pointer"
                                                @click="viewSalary(s.id)">
                                            </i>

                                            <!-- Edit -->
                                            <i class="fa-solid fa-pen-to-square text-primary" style="cursor: pointer"
                                                @click="editSalary(s.id)">
                                            </i>
                                        </a-space>
                                    </td>
                                </tr>

                                <tr v-if="salaries.length === 0">
                                    <td colspan="6" class="text-center text-muted">
                                        Không có dữ liệu lương
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <a-modal :open="showModal" @update:open="(value) => showModal = value" title="Chi tiết / chỉnh sửa lương"
            ok-text="Lưu" cancel-text="Đóng" @ok="saveSalary" :ok-button-props="{
                disabled: isLocked,
                style: { display: mode === 'view' ? 'none' : 'inline-block' }
            }">
            <div v-if="selectedSalary">

                <p><b>Nhân viên:</b> {{ selectedSalary.employee.user.name }}</p>
                <p><b>Tháng:</b> {{ selectedSalary.month }} / {{ selectedSalary.year }}</p>

                <p>
                    <b>Trạng thái:</b>

                    <span :class="selectedSalary.status === 'published'
                        ? 'badge bg-success'
                        : 'badge bg-secondary'">
                        {{
                            selectedSalary.status === 'published'
                                ? 'Đã công bố'
                        : 'Nháp'
                        }}
                    </span>
                </p>

                <div class="row g-3 mt-2">
                    <div class="col-12">
                        <label>Lương cơ bản</label>
                        <input v-model="selectedSalary.base_salary" type="number" class="form-control"
                            :disabled="isLocked" />
                    </div>

                    <div class="col-12">
                        <label>Thưởng</label>
                        <input v-model="selectedSalary.bonus" type="number" class="form-control" :disabled="isLocked" />
                    </div>

                    <div class="col-12">
                        <label>Thuế</label>
                        <input v-model="selectedSalary.tax" type="number" class="form-control" :disabled="isLocked" />
                    </div>

                    <div class="col-12">
                        <label>Ghi chú</label>
                        <textarea v-model="selectedSalary.note" class="form-control" :disabled="isLocked"></textarea>
                    </div>
                </div>

                <!-- TOTAL -->
                <div class="mt-3">
                    <h4>
                        Total:
                        <span class="text-success">{{ total }}</span>
                    </h4>
                </div>

            </div>
        </a-modal>
    </div>
</template>


<script setup>
import SidebarAccountant from '../../components/SidebarAccountant.vue'
import { ref, onMounted, computed } from 'vue'
import http from '../../services/http'

const showSidebar = ref(false)

const showModal = ref(false)

const salaries = ref([])
const month = ref(4)
const year = ref(2026)

const search = ref('')
const status = ref('')

const selectedSalary = ref(null)

const mode = ref('view') // 'view' | 'edit'

const loadSalaries = async () => {
    const res = await http.get('/accountant/salaries', {
        params: {
            month: month.value,
            year: year.value,
            search: search.value,
            status: status.value
        }
    })

    salaries.value = res.data
}

const publish = async () => {
    await http.post('/accountant/salary/publish', {
        month: month.value,
        year: year.value
    })
    loadSalaries()
}

const viewSalary = async (id) => {
    const res = await http.get(`/accountant/salary/${id}`)
    selectedSalary.value = res.data
    mode.value = 'view'
    showModal.value = true
}

const editSalary = async (id) => {
    const res = await http.get(`/accountant/salary/${id}`)
    selectedSalary.value = res.data
    mode.value = 'edit'
    showModal.value = true
}

const total = computed(() => {
    if (!selectedSalary.value) return 0

    return (
        Number(selectedSalary.value.base_salary || 0) +
        Number(selectedSalary.value.bonus || 0) -
        Number(selectedSalary.value.tax || 0)
    )
})

const isLocked = computed(() => {
    return (
        selectedSalary.value?.status === 'published' ||
        mode.value === 'view'
    )
})

const closeModal = () => {
    showModal.value = false
    selectedSalary.value = null
}

const saveSalary = async () => {
    try {
        const res = await http.put(
            `/accountant/salary/${selectedSalary.value.id}`,
            selectedSalary.value
        )

        selectedSalary.value = null
        showModal.value = false

        await loadSalaries()

        alert('Cập nhật thành công')
    } catch (error) {
        alert(error.response?.data?.message || 'Lỗi')
    }
}

const publishOne = async (id) => {
    await http.post(`/accountant/salary/${id}/publish`)
    loadSalaries()
}

const exportExcel = async () => {
    try {
        const res = await http.post(
            '/accountant/salary/export',
            {
                month: month.value,
                year: year.value
            },
            {
                responseType: 'blob' // server trả về file, nên set responseType là blob để nhận dạng đúng
            }
        )

        // tạo URL tạm thời cho file vừa nhận được để có thể tải về
        const url = window.URL.createObjectURL(new Blob([res.data]))
        // thẻ a ảo để tải file về vì brower chỉ cho phép tải file qua thẻ a có thuộc tính download
        const link = document.createElement('a')

        // trỏ tới file excel vừa được tạo
        link.href = url
        // đặt tên file khi tải về
        link.setAttribute(
            'download',
            `salary_${month.value}_${year.value}.xlsx`
        )

        // Thêm vào DOM để đảm bảo click hoạt động 
        document.body.appendChild(link)
        // Tự động click để download
        link.click()
        // Xóa link sau khi tải xong
        link.remove()
    } catch (error) {
        alert('Xuất file thất bại')
    }
}

const createSalary = async () => {
    if (!confirm(`Tạo bảng lương tháng ${month.value}/${year.value}?`)) {
        return
    }

    try {
        await http.post('/accountant/salary/create', {
            month: month.value,
            year: year.value
        })

        alert('Tạo bảng lương thành công')
        loadSalaries()
    } catch (error) {
        alert(error.response?.data?.message || 'Lỗi')
    }
}

onMounted(() => {
    loadSalaries()
})

</script>

<style scoped>
.table td,
.table th {
    vertical-align: middle;
}
</style>