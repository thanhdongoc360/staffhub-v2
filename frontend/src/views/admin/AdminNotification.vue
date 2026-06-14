<template>
    <div>
        <div class="container-fluid">
            <a-button @click="showSidebar = true" class="d-lg-none mb-2">
                <i class="fa-solid fa-bars"></i>
            </a-button>

            <a-drawer :visible="showSidebar" placement="left" width="260" @close="showSidebar = false" class="d-lg-none">
                <SidebarAdmin />
            </a-drawer>

            <div class="row">
                <div class="d-none d-lg-block col-lg-3">
                    <SidebarAdmin />
                </div>

                <div class="col-12 col-lg-9">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mt-3 gap-2">
                                <h1 class="mb-0">Thông báo</h1>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <a-button style="background-color: yellow; border-color: yellow;" @click="showUnreadOnly">Chưa đọc</a-button>
                                    <a-button @click="showAllNotifications">Tất cả</a-button>
                                    <a-button @click="markAllAsRead">
                                        <i class="fa-solid fa-check-double me-1"></i>Đánh dấu là đã đọc
                                    </a-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="d-none d-lg-table-cell" scope="col">Nhân viên</th>
                                    <th scope="col">Tiêu đề</th>
                                    <th class="d-none d-lg-table-cell" scope="col">Nội dung</th>
                                    <th scope="col">Thời gian</th>
                                    <th scope="col">Trạng thái</th>
                                    <th scope="col">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="noti in filteredNotifications" :key="noti.id">
                                    <td class="d-none d-lg-table-cell">{{ noti.user?.name }}</td>
                                    <td>{{ noti.title }}</td>
                                    <td class="d-none d-lg-table-cell">{{ noti.content }}</td>
                                    <td>{{ formatDate(noti.date) }}</td>
                                    <td>
                                        <span v-if="!noti.is_read" class="text-danger">Chưa đọc</span>
                                        <span v-else class="text-success">Đã đọc</span>
                                    </td>
                                    <td>
                                        <a-button @click="markAsRead(noti)" :disabled="noti.is_read" :class="{ 'opacity-50': noti.is_read }">Đánh dấu là đã đọc</a-button>
                                    </td>
                                </tr>

                                <tr v-if="filteredNotifications.length === 0">
                                    <td colspan="6" class="text-center text-muted">
                                        {{ emptyMessage }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <a-pagination
                            :current="currentPage"
                            :total="totalNotifications"
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
import SidebarAdmin from '../../components/SidebarAdmin.vue';

import { ref, onMounted, watch } from 'vue'
import http from "../../services/http";

const notifications = ref([])
const showSidebar = ref(false)
const filterType = ref('all')
const currentPage = ref(1)
const totalNotifications = ref(0)
const perPage = 10
const emptyMessage = ref('Không có thông báo nào')

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN')
}

const fetchNotifications = async (page = 1) => {
    try {
        const res = await http.get('/notifications', {
            params: {
                page,
                per_page: perPage,
                filter_type: filterType.value === 'unread' ? 'unread' : undefined
            }
        })

        const payload = res.data.data ?? {}

        notifications.value = payload.data ?? []
        totalNotifications.value = payload.total ?? 0
        currentPage.value = payload.current_page ?? page

        if (filterType.value === 'unread') {
            emptyMessage.value = 'Không có thông báo chưa đọc'
        } else {
            emptyMessage.value = 'Không có thông báo nào'
        }
    } catch (error) {
        console.log('Lỗi tải thông báo', error)
    }
}

const showUnreadOnly = () => {
    filterType.value = 'unread'
}

const showAllNotifications = () => {
    filterType.value = 'all'
}

onMounted(() => {
    fetchNotifications()
})

const filteredNotifications = notifications

const onPageChange = (page) => {
    currentPage.value = page
    fetchNotifications(page)
}

const markAsRead = async (noti) => {
    if (noti.is_read) return

    try {
        await http.put(`/notifications/${noti.id}/read`)

        await fetchNotifications(currentPage.value)
    } catch (error) {
        console.log('Lỗi cập nhật trạng thái', error)
    }
}

const markAllAsRead = async () => {
    try {
        await http.put('/notifications/read-all')

        currentPage.value = 1
        await fetchNotifications(1)
    } catch (error) {
        console.log('Lỗi đọc tất cả', error)
    }
}

watch(filterType, () => {
    currentPage.value = 1
    fetchNotifications(1)
})


</script>