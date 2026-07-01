<template>
    <nav class="navbar w-100" style="background-color: #1E90FF;">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold fs-4" style="cursor: pointer;">
                <span style="color: white;" @click="goHome">
                    StaffHub
                </span>
            </span>

            <div class="d-flex align-items-center gap-4">
                <div v-if="user" class="text-end">
                    <div class="user-name">
                        {{ user.name }}
                    </div>

                    <div class="user-role">
                        {{ roleLabel }}
                    </div>
                </div>

                <button class="btn btn-outline-light" @click="handleLogout">
                    Đăng xuất
                </button>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import http from "../services/http";

const router = useRouter()

const user = JSON.parse(localStorage.getItem('user'))

const roleLabel = computed(() => {
    switch (user?.role) {
        case 'admin':
            return 'Quản trị viên'
        case 'employee':
            return 'Nhân viên'
        case 'management':
            return 'Quản lý'
        case 'accountant':
            return 'Kế toán'
        default:
            return ''
    }
})

const goHome = () => {
    if (!user || !user.role) {
        router.push('/login')
        return
    }

    switch(user.role) {
        case 'admin':
            router.push('/admin/dashboard')
            break
        case 'employee':
            router.push('/employee/dashboard')
            break
        case 'management':
            router.push('/management/dashboard')
            break
        case 'accountant':
            router.push('/accountant/dashboard')
            break
        default:
            router.push('/login')
    }
}

const handleLogout = async () => {
    try {
        await http.post('/logout')

        localStorage.removeItem('token')
        localStorage.removeItem('user')

        router.push('/login')
    }
    catch (error) {
        console.log(error)
    }
}
</script>

<style scoped>
.user-name {
    color: #fff;
    font-weight: 600;
    font-size: 15px;
    line-height: 1.2;
}

.user-role {
    color: rgba(255,255,255,.8);
    font-size: 13px;
}
</style>