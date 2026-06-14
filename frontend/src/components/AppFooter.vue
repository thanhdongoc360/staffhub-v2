<template>
  <footer class="app-footer">
    <div class="app-footer__content">
      <div class="app-footer__left">
        © {{ currentYear }} StaffHub
      </div>

      <div class="app-footer__right">
        Hệ thống quản lý nhân sự nội bộ
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.app-footer {
  width: 100%;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  margin-top: 24px;
}

.app-footer__content {
  height: 60px;

  display: flex;
  align-items: center;
  justify-content: space-between;

  padding: 0 32px;

  font-size: 14px;
  color: #64748b;
}

.app-footer__left {
  font-weight: 600;
  color: #334155;
}

.app-footer__right {
  color: #94a3b8;
}

@media (max-width: 768px) {
  .app-footer__content {
    height: auto;
    padding: 16px;

    flex-direction: column;
    gap: 6px;

    text-align: center;
  }
}
</style>