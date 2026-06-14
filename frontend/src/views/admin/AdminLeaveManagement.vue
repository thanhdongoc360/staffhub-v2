<template>
    <div>
        <div class="container-fluid">
            <a-button @click="showSidebar = true" class="d-lg-none mb-2">
                <i class="fa-solid fa-bars"></i>
            </a-button>

            <a-drawer :visible="showSidebar" placement="left" width="260" @close="showSidebar = false"
                class="d-lg-none">
                <SidebarAdmin />
            </a-drawer>

            <div class="row">
                <div class="d-none d-lg-block col-lg-3">
                    <SidebarAdmin />
                </div>

                <div class="col-12 col-lg-9">
                    <div class="d-flex justify-content-between align-items-center mt-2 mt-lg-3 gap-2">
                        <h1 class="mb-0">Quản lý đơn nghỉ phép</h1>
                    </div>

                    <div class="row mt-3 gap-2">
                        <div class="col-12 col-md-6">
                            <a-input-search 
                                v-model:value="searchKeyword" 
                                placeholder="Tìm theo tên nhân viên, loại nghỉ, lý do..."
                                @search="() => { currentPage = 1; fetchLeaves(1) }"
                                enter-button
                            />
                        </div>
                        <div class="col-12 col-md-4">
                            <a-select 
                                v-model:value="filterStatus" 
                                placeholder="Lọc theo trạng thái"
                                allow-clear
                                @change="() => { currentPage = 1; fetchLeaves(1) }"
                            >
                                <a-select-option value="Chờ duyệt">Chờ duyệt</a-select-option>
                                <a-select-option value="Đã duyệt">Đã duyệt</a-select-option>
                                <a-select-option value="Từ chối">Từ chối</a-select-option>
                            </a-select>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Tên nhân viên</th>
                                    <th scope="col">Loại nghỉ</th>
                                    <th class="d-none d-md-table-cell" scope="col">Ngày bắt đầu</th>
                                    <th class="d-none d-md-table-cell" scope="col">Ngày kết thúc</th>
                                    <th class="d-none d-lg-table-cell" scope="col">Lý do</th>
                                    <th class="d-none d-lg-table-cell" scope="col">Trạng thái</th>
                                    <th scope="col">Hành động</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="leave in leaves" :key="leave.id">
                                    <td>{{ leave.employee?.user?.name }}</td>
                                    <td>{{ leave.type }}</td>
                                    <td class="d-none d-md-table-cell">{{ leave.start_date }}</td>
                                    <td class="d-none d-md-table-cell">{{ leave.end_date }}</td>
                                    <td class="d-none d-lg-table-cell">{{ leave.reason }}</td>
                                    <td class="d-none d-lg-table-cell">
                                        <span :class="statusClass(leave.status)">{{ statusText(leave.status) }}</span>
                                    </td>
                                    <td>
                                        <template v-if="leave.status === 'Chờ duyệt'">
                                            <a-button class="me-2" type="primary" @click="approve(leave.id)">
                                                <i class="fa-solid fa-check me-1"></i>Duyệt
                                            </a-button>

                                            <a-button type="primary" danger @click="reject(leave.id)">
                                                <i class="fa-solid fa-x me-1"></i>Từ chối
                                            </a-button>
                                        </template>

                                        <template v-else-if="leave.status === 'Đã duyệt'">
                                            <span class="text-success fw-bold">Đã duyệt</span>
                                        </template>

                                        <template v-else-if="leave.status === 'Từ chối'">
                                            <span class="text-danger fw-bold">Đã từ chối</span>
                                        </template>
                                    </td>
                                </tr>
                                <tr v-if="leaves.length === 0">
                                    <td colspan="8" class="text-center text-muted">Chưa có đơn nghỉ phép nào</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <a-pagination 
                            v-model:current="currentPage" 
                            :total="totalLeaves" 
                            :page-size="perPage"
                            @change="onPageChange"
                            show-less-items
                        />
                    </div>
                </div>


            </div>
        </div>
    </div>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import http from "../../services/http";
import SidebarAdmin from '../../components/SidebarAdmin.vue'

const leaves = ref([])
const showSidebar = ref(false)
const currentPage = ref(1)
const totalLeaves = ref(0)
const perPage = 10
const searchKeyword = ref('')
const filterStatus = ref(null)

const fetchLeaves = async (page = 1) => {
    try {
        const response = await http.get('/admin/leaves', {
            params: {
                page: page,
                per_page: perPage,
                search: searchKeyword.value || undefined,
                status: filterStatus.value || undefined
            }
        })
        leaves.value = response.data.data.data || []
        totalLeaves.value = response.data.data.total || 0
    } catch (error) {
        console.log('Không tải được danh sách đơn nghỉ phép:', error)
        leaves.value = []
    }
}

const onPageChange = (page) => {
    currentPage.value = page
    fetchLeaves(page)
}

const approve = async (id) => {
    try {
        await http.post(`/admin/leaves/${id}/approve`);
        currentPage.value = 1;
        fetchLeaves(1)
    } catch (error) {
        console.log('Không duyệt được đơn nghỉ:', error)
    }
}

const reject = async (id) => {
    try {
        await http.post(`/admin/leaves/${id}/reject`)
        currentPage.value = 1;
        fetchLeaves(1)
    } catch (error) {
        console.log('Không từ chối được đơn nghỉ:', error)
    }
}

const statusText = (status) => {
    if (status === 'Chờ duyệt') return 'Chờ duyệt'
    if (status === 'Đã duyệt') return 'Đã duyệt'
    if (status === 'Từ chối') return 'Từ chối'
    return status || 'Không xác định'
}

const statusClass = (status) => {
    if (status === 'Chờ duyệt') return 'text-warning fw-bold'
    if (status === 'Đã duyệt') return 'text-success fw-bold'
    if (status === 'Từ chối') return 'text-danger fw-bold'
    return 'text-muted fw-bold'
}

onMounted(() => {
    fetchLeaves(currentPage.value)
})

</script>